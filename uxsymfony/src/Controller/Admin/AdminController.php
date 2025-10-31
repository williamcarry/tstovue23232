<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\PromotionMenu;
use App\Entity\Role;
use App\Repository\AdminRepository;
use App\Repository\RoleRepository;
use App\Service\PathConfigService;
use App\Service\QiniuUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin{adminLoginPath}', requirements: ['adminLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('ADMIN_ACTIVE')]
class AdminController extends AbstractController
{
    #[Route('/user/list/tab', name: 'admin_user_list_tab')]
    #[IsGranted('SUPER_ADMIN')]
    public function userListTab(Request $request, string $adminLoginPath, AdminRepository $adminRepository, RoleRepository $roleRepository)
    {
        // 获取所有管理员
        $admins = $adminRepository->findAll();
        
        // 转换管理员数据以适应前端组件
        $adminData = [];
        foreach ($admins as $admin) {
            // 获取角色信息
            $roles = $admin->getRoles();
            
            // 过滤掉基础角色
            $filteredRoles = array_filter($roles, function($role) {
                return $role !== 'ROLE_ADMIN' && $role !== 'ROLE_SUPER_ADMIN';
            });
            
            // 如果没有特殊角色，显示空字符串
            $roleLabel = '';
            if (!empty($filteredRoles)) {
                // 获取所有admin类型的角色映射关系
                $adminRoles = $roleRepository->findBy(['type' => 'admin']);
                $roleLabelMapping = [];
                foreach ($adminRoles as $adminRole) {
                    $roleLabelMapping[$adminRole->getName()] = $adminRole->getDescription();
                }
                
                // 默认角色标签映射
                $defaultRoleLabelMapping = [
                    'ROLE_SUPER_ADMIN' => '超级管理员',
                    'ROLE_ADMIN' => '管理员'
                ];
                
                // 合并默认映射和数据库映射
                $roleLabelMapping = array_merge($defaultRoleLabelMapping, $roleLabelMapping);
                
                // 根据角色设置标签，显示所有匹配的角色
                $roleLabels = [];
                foreach ($filteredRoles as $role) {
                    if (isset($roleLabelMapping[$role])) {
                        $roleLabels[] = $roleLabelMapping[$role];
                    }
                }
                
                $roleLabel = implode(', ', $roleLabels);
            }
            
            // 获取管理员关联的角色
            $adminRoles = [];
            // AdminRole功能已移除
            
            $adminData[] = [
                'id' => $admin->getId(),
                'username' => $admin->getUsername(),  // 添加username字段
                'name' => $admin->getMark() ?: $admin->getUsername(),
                'email' => $admin->getEmail(),
                'role' => $roleLabel,
                'roles' => $roles // 添加管理员实际的角色数组
            ];
        }
        
        // 获取type为admin的角色列表
        $roles = $roleRepository->findBy(['type' => 'admin']);
        $roleData = [];
        foreach ($roles as $role) {
            $roleData[] = [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'description' => $role->getDescription(),
                'type' => $role->getType()
            ];
        }
        
        // 检查是否是 AJAX 请求
        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            // 返回 JSON 格式的管理员数据
            return new JsonResponse([
                'admins' => $adminData,
                'roles' => $roleData
            ]);
        }
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/_admin_list_inner.html.twig', [
            'admins' => $adminData,
            'roles' => $roleData
        ]);
    }

    #[Route('/user/create', name: 'admin_user_create', methods: ['POST'])]
    #[IsGranted('SUPER_ADMIN')]
    public function createUser(Request $request, string $adminLoginPath, AdminRepository $adminRepository, RoleRepository $roleRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        // 检查是否是 AJAX 请求
        if ($request->headers->get('X-Requested-With') !== 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => false,
                'error' => '只允许 AJAX 请求'
            ], 400);
        }
        
        try {
            // 获取请求数据
            $data = json_decode($request->getContent(), true);
            
            // 添加调试日志
            error_log('Create user - Received data: ' . print_r($data, true));
            
            // 创建新管理员
            $newAdmin = new Admin();
            
            // 设置基本信息
            if (!isset($data['username']) || empty($data['username'])) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '用户名不能为空'
                ], 400);
            }
            
            // 检查用户名唯一性
            $existingAdmin = $adminRepository->findOneBy(['username' => $data['username']]);
            if ($existingAdmin) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '该用户名已被使用'
                ], 400);
            }
            $newAdmin->setUsername($data['username']);
            
            if (!isset($data['email']) || empty($data['email'])) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '邮箱不能为空'
                ], 400);
            }
            
            // 检查邮箱唯一性
            $existingAdmin = $adminRepository->findOneBy(['email' => $data['email']]);
            if ($existingAdmin) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '该邮箱已被使用'
                ], 400);
            }
            $newAdmin->setEmail($data['email']);
            
            if (isset($data['name'])) {
                $newAdmin->setMark($data['name']);
            }
            
            // 设置密码 - 优先使用用户输入的密码，否则使用默认密码
            $password = $defaultPassword = '12345678'; // 默认密码
            if (isset($data['password']) && !empty($data['password'])) {
                $password = $data['password'];
            }
            $newAdmin->setPassword($passwordHasher->hashPassword($newAdmin, $password));
            
            // 设置系统角色
            $roles = ['ROLE_ADMIN']; // 始终包含默认的ROLE_ADMIN角色
            // 只有在明确选择超级管理员角色时才添加ROLE_SUPER_ADMIN权限
            
            if (isset($data['role'])) {
                // 获取所有admin类型的角色映射关系
                $adminRoles = $roleRepository->findBy(['type' => 'admin']);
                $roleMapping = [];
                foreach ($adminRoles as $adminRole) {
                    $roleMapping[$adminRole->getDescription()] = $adminRole->getName();
                }
                
                // 默认角色映射（移除超级管理员）
                $defaultRoleMapping = [
                    '管理员' => 'ROLE_ADMIN',
                    '超级管理员' => 'ROLE_SUPER_ADMIN'
                ];
                
                // 合并默认映射和数据库映射
                $roleMapping = array_merge($defaultRoleMapping, $roleMapping);
                
                if (is_string($data['role'])) {
                    // 单选角色
                    if (isset($roleMapping[$data['role']])) {
                        // 只有非系统默认角色才添加
                        if ($roleMapping[$data['role']] !== 'ROLE_ADMIN' && $roleMapping[$data['role']] !== 'ROLE_SUPER_ADMIN') {
                            $roles[] = $roleMapping[$data['role']];
                        } else if ($roleMapping[$data['role']] === 'ROLE_SUPER_ADMIN') {
                            // 只有明确选择了超级管理员角色才添加ROLE_SUPER_ADMIN
                            $roles[] = 'ROLE_SUPER_ADMIN';
                        }
                    } else {
                        // 如果没有找到映射，直接使用角色名称（可能是ROLE_开头的系统角色）
                        $roles[] = $data['role'];
                    }
                } elseif (is_array($data['role'])) {
                    // 多选角色
                    foreach ($data['role'] as $role) {
                        if (isset($roleMapping[$role])) {
                            // 只有非系统默认角色才添加
                            if ($roleMapping[$role] !== 'ROLE_ADMIN' && $roleMapping[$role] !== 'ROLE_SUPER_ADMIN') {
                                $roles[] = $roleMapping[$role];
                            } else if ($roleMapping[$role] === 'ROLE_SUPER_ADMIN') {
                                // 只有明确选择了超级管理员角色才添加ROLE_SUPER_ADMIN
                                $roles[] = 'ROLE_SUPER_ADMIN';
                            }
                        } else {
                            // 如果没有找到映射，直接使用角色名称（可能是ROLE_开头的系统角色）
                            $roles[] = $role;
                        }
                    }
                }
            } 
            
            $newAdmin->setRoles($roles);
            
            // 设置角色权限
            if (isset($data['roles']) && is_array($data['roles'])) {
                foreach ($data['roles'] as $roleId) {
                    $role = $roleRepository->find($roleId);
                    if ($role && $role->getType() === 'admin') {
                    }
                }
            }
            
            // 设置激活状态
            $newAdmin->setIsActive(true);
            
            // 保存到数据库
            $entityManager->persist($newAdmin);
            $entityManager->flush();
            
            // 根据使用的密码类型返回相应的消息
            $message = '管理员创建成功';
            if ($password === $defaultPassword) {
                $message .= '，默认密码为: ' . $defaultPassword;
            } else {
                $message .= '，密码已设置';
            }
            
            return new JsonResponse([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '创建过程中发生错误: ' . $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/user/update/{id}', name: 'admin_user_update', methods: ['POST'])]
    #[IsGranted('SUPER_ADMIN')]
    public function updateUser(int $id, Request $request, string $adminLoginPath, AdminRepository $adminRepository, RoleRepository $roleRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        // 检查是否是 AJAX 请求
        if ($request->headers->get('X-Requested-With') !== 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => false,
                'error' => '只允许 AJAX 请求'
            ], 400);
        }
        
        // 获取要更新的管理员
        $adminToUpdate = $adminRepository->find($id);
        if (!$adminToUpdate) {
            return new JsonResponse([
                'success' => false,
                'error' => '管理员不存在'
            ], 404);
        }
        
        // 检查是否尝试修改超级管理员（如果不是超级管理员操作）
        /** @var Admin $currentAdmin */
        $currentAdmin = $this->getUser();
        $isCurrentUserSuperAdmin = in_array('ROLE_SUPER_ADMIN', $currentAdmin->getRoles());
        $isTargetUserSuperAdmin = in_array('ROLE_SUPER_ADMIN', $adminToUpdate->getRoles());
        
        if ($isTargetUserSuperAdmin && !$isCurrentUserSuperAdmin) {
            return new JsonResponse([
                'success' => false,
                'error' => '无法修改超级管理员账户'
            ], 403);
        }
        
        try {
            // 获取请求数据
            $data = json_decode($request->getContent(), true);
            
            // 更新基本信息
            if (isset($data['email'])) {
                // 验证邮箱唯一性
                $existingAdmin = $adminRepository->findOneBy(['email' => $data['email']]);
                if ($existingAdmin && $existingAdmin->getId() !== $adminToUpdate->getId()) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => '该邮箱已被其他管理员使用'
                    ], 400);
                }
                $adminToUpdate->setEmail($data['email']);
            }
            
            // 只有超级管理员可以修改mark字段
            if (isset($data['name']) && $isCurrentUserSuperAdmin) {
                $adminToUpdate->setMark($data['name']);
            }
            
            // 更新系统角色（只有超级管理员可以修改角色）
            if (isset($data['role']) && $isCurrentUserSuperAdmin) {
                $roles = ['ROLE_ADMIN']; // 始终包含默认的ROLE_ADMIN角色
                // 只有在明确选择超级管理员角色时才添加ROLE_SUPER_ADMIN权限
                
                // 获取所有admin类型的角色映射关系
                $adminRoles = $roleRepository->findBy(['type' => 'admin']);
                $roleMapping = [];
                foreach ($adminRoles as $adminRole) {
                    $roleMapping[$adminRole->getDescription()] = $adminRole->getName();
                }
                
                // 默认角色映射（移除超级管理员）
                $defaultRoleMapping = [
                    '管理员' => 'ROLE_ADMIN',
                    '超级管理员' => 'ROLE_SUPER_ADMIN'
                ];
                
                // 合并默认映射和数据库映射
                $roleMapping = array_merge($defaultRoleMapping, $roleMapping);
                
                if (is_string($data['role'])) {
                    // 单选角色
                    if (isset($roleMapping[$data['role']])) {
                        // 只有非系统默认角色才添加
                        if ($roleMapping[$data['role']] !== 'ROLE_ADMIN' && $roleMapping[$data['role']] !== 'ROLE_SUPER_ADMIN') {
                            $roles[] = $roleMapping[$data['role']];
                        } else if ($roleMapping[$data['role']] === 'ROLE_SUPER_ADMIN') {
                            // 只有明确选择了超级管理员角色才添加ROLE_SUPER_ADMIN
                            $roles[] = 'ROLE_SUPER_ADMIN';
                        }
                    } else {
                        // 如果没有找到映射，直接使用角色名称（可能是ROLE_开头的系统角色）
                        $roles[] = $data['role'];
                    }
                } elseif (is_array($data['role'])) {
                    // 多选角色
                    foreach ($data['role'] as $role) {
                        if (isset($roleMapping[$role])) {
                            // 只有非系统默认角色才添加
                            if ($roleMapping[$role] !== 'ROLE_ADMIN' && $roleMapping[$role] !== 'ROLE_SUPER_ADMIN') {
                                $roles[] = $roleMapping[$role];
                            } else if ($roleMapping[$role] === 'ROLE_SUPER_ADMIN') {
                                // 只有明确选择了超级管理员角色才添加ROLE_SUPER_ADMIN
                                $roles[] = 'ROLE_SUPER_ADMIN';
                            }
                        } else {
                            // 如果没有找到映射，直接使用角色名称（可能是ROLE_开头的系统角色）
                            $roles[] = $role;
                        }
                    }
                }
                
                // 如果目标用户是超级管理员，确保他们始终拥有ROLE_SUPER_ADMIN权限
                if ($isTargetUserSuperAdmin) {
                    // 确保超级管理员角色不被移除
                    if (!in_array('ROLE_SUPER_ADMIN', $roles)) {
                        $roles[] = 'ROLE_SUPER_ADMIN';
                    }
                } else {
                    // 如果目标用户不是超级管理员，确保不会被分配超级管理员角色
                    if (in_array('ROLE_SUPER_ADMIN', $roles)) {
                        // 移除超级管理员角色
                        $roles = array_filter($roles, function($role) {
                            return $role !== 'ROLE_SUPER_ADMIN';
                        });
                    }
                }
                
                $adminToUpdate->setRoles($roles);
            }
            
            // 更新角色权限
            // AdminRole功能已移除
            
            // 处理密码修改
            if (isset($data['password']) && !empty($data['password'])) {
                // 验证密码长度
                if (strlen($data['password']) < 8) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => '密码必须至少包含8个字符'
                    ], 400);
                }
                
                // 设置新密码
                $adminToUpdate->setPassword($passwordHasher->hashPassword($adminToUpdate, $data['password']));
            }
            
            // 保存到数据库
            $entityManager->persist($adminToUpdate);
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => '管理员信息更新成功'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '更新过程中发生错误: ' . $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/user/delete/{id}', name: 'admin_user_delete', methods: ['POST'])]
    #[IsGranted('SUPER_ADMIN')]
    public function deleteUser(int $id, Request $request, string $adminLoginPath, AdminRepository $adminRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // 检查是否是 AJAX 请求
        if ($request->headers->get('X-Requested-With') !== 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => false,
                'error' => '只允许 AJAX 请求'
            ], 400);
        }
        
        // 获取要删除的管理员
        $adminToDelete = $adminRepository->find($id);
        if (!$adminToDelete) {
            return new JsonResponse([
                'success' => false,
                'error' => '管理员不存在'
            ], 404);
        }
        
        // 检查是否尝试删除超级管理员
        if (in_array('ROLE_SUPER_ADMIN', $adminToDelete->getRoles())) {
            return new JsonResponse([
                'success' => false,
                'error' => '无法删除超级管理员账户'
            ], 403);
        }
        
        // 检查是否尝试删除自己的账户
        /** @var Admin $currentAdmin */
        $currentAdmin = $this->getUser();
        if ($currentAdmin->getId() === $adminToDelete->getId()) {
            return new JsonResponse([
                'success' => false,
                'error' => '无法删除自己的账户'
            ], 403);
        }
        
        try {
            // 删除管理员
            $entityManager->remove($adminToDelete);
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => '管理员删除成功'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '删除过程中发生错误: ' . $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/profile/edit', name: 'admin_profile_edit')]
    public function editProfile(Request $request, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, EntityManagerInterface $entityManager, string $adminLoginPath): Response
    {
        // 获取当前管理员用户
        /** @var Admin $admin */
        $admin = $this->getUser();
       
        // 保存原始邮箱，用于验证是否更改
        $originalEmail = $admin->getEmail();
        
        // 处理表单提交
        if ($request->isMethod('POST')) {
            // 检查是否是 AJAX 请求
            $isAjax = $request->headers->get('X-Requested-With') === 'XMLHttpRequest';
            
            // 获取表单数据
            $email = $request->request->get('email');
            $mark = $request->request->get('mark');
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');
            
            // 更新基本信息（邮箱和标记）
            $admin->setEmail($email);
            $admin->setMark($mark);
            
            // 验证邮箱唯一性（如果邮箱有更改）
            if ($email !== $originalEmail) {
                $existingAdmin = $entityManager->getRepository(Admin::class)->findOneBy(['email' => $email]);
                if ($existingAdmin && $existingAdmin->getId() !== $admin->getId()) {
                    $error = '该邮箱已被其他管理员使用';
                    if ($isAjax) {
                        return new JsonResponse([
                            'success' => false,
                            'errors' => [$error]
                        ]);
                    }
                    $this->addFlash('error', $error);
                    return $this->render('admin/_profile_edit_inner.html.twig', [
                        'admin' => $admin,
                    ]);
                }
            }
            
            // 验证数据
            $errors = $validator->validate($admin);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                if ($isAjax) {
                    return new JsonResponse([
                        'success' => false,
                        'errors' => $errorMessages
                    ]);
                }
                foreach ($errorMessages as $errorMessage) {
                    $this->addFlash('error', $errorMessage);
                }
                return $this->render('admin/_profile_edit_inner.html.twig', [
                    'admin' => $admin,
                ]);
            }
            
            // 处理密码修改 - 只有当新密码和确认密码都不为空时才修改密码
            if (!empty($newPassword) && !empty($confirmPassword)) {
                // 验证当前密码
                if (empty($currentPassword)) {
                    $error = '请输入当前密码以修改密码';
                    if ($isAjax) {
                        return new JsonResponse([
                            'success' => false,
                            'errors' => [$error]
                        ]);
                    }
                    $this->addFlash('error', $error);
                    return $this->render('admin/_profile_edit_inner.html.twig', [
                        'admin' => $admin,
                    ]);
                }
                
                // 验证当前密码是否正确
                if (!$passwordHasher->isPasswordValid($admin, $currentPassword)) {
                    $error = '当前密码不正确';
                    if ($isAjax) {
                        return new JsonResponse([
                            'success' => false,
                            'errors' => [$error]
                        ]);
                    }
                    $this->addFlash('error', $error);
                    return $this->render('admin/_profile_edit_inner.html.twig', [
                        'admin' => $admin,
                    ]);
                }
                
                // 验证新密码和确认密码是否一致
                if ($newPassword !== $confirmPassword) {
                    $error = '新密码和确认密码不一致';
                    if ($isAjax) {
                        return new JsonResponse([
                            'success' => false,
                            'errors' => [$error]
                        ]);
                    }
                    $this->addFlash('error', $error);
                    return $this->render('admin/_profile_edit_inner.html.twig', [
                        'admin' => $admin,
                    ]);
                }
                
                // 验证新密码长度（至少8个字符）
                if (strlen($newPassword) < 8) {
                    $error = '新密码至少需要8个字符';
                    if ($isAjax) {
                        return new JsonResponse([
                            'success' => false,
                            'errors' => [$error]
                        ]);
                    }
                    $this->addFlash('error', $error);
                    return $this->render('admin/_profile_edit_inner.html.twig', [
                        'admin' => $admin,
                    ]);
                }
                
                // 设置新密码
                $admin->setPassword($passwordHasher->hashPassword($admin, $newPassword));
            } elseif (!empty($newPassword) || !empty($confirmPassword)) {
                // 如果只填写了其中一个密码字段，显示错误信息
                $error = '新密码和确认密码必须同时填写';
                if ($isAjax) {
                    return new JsonResponse([
                        'success' => false,
                        'errors' => [$error]
                    ]);
                }
                $this->addFlash('error', $error);
                return $this->render('admin/_profile_edit_inner.html.twig', [
                    'admin' => $admin,
                ]);
            }
            
            // 保存到数据库
            $entityManager->persist($admin);
            $entityManager->flush();
            
            // 成功响应
            if ($isAjax) {
                return new JsonResponse([
                    'success' => true,
                    'message' => '个人信息更新成功'
                ]);
            }
            
            $this->addFlash('success', '个人信息更新成功');
            return $this->redirectToRoute('admin_profile_edit', ['adminLoginPath' => $adminLoginPath]);
        }
        
        return $this->render('admin/_profile_edit_inner.html.twig', [
            'admin' => $admin,
        ]);
    }
    
    #[Route('/role/list/tab', name: 'admin_role_list_tab')]
    #[IsGranted('SUPER_ADMIN')]
    public function roleListTab(Request $request, string $adminLoginPath, RoleRepository $roleRepository)
    {
        // 获取所有角色
        $roles = $roleRepository->findAll();
        
        // 类型映射
        $typeMapping = [
            'admin' => '管理员后台权限',
            'supplier' => '供应商后台权限'
        ];
        
        // 转换角色数据以适应前端组件
        $roleData = [];
        foreach ($roles as $role) {
            // 获取权限数量
            $permissionCount = 0; // 权限功能已移除
            
            // 转换类型显示
            $type = $role->getType();
            $displayType = isset($typeMapping[$type]) ? $typeMapping[$type] : $type;
            
            $roleData[] = [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'description' => $role->getDescription(),
                'type' => $displayType,
                'permissionCount' => $permissionCount
            ];
        }
        
        // 获取所有权限
        $permissionData = [];
        // 权限功能已移除
        
        // 检查是否是 AJAX 请求
        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            // 返回 JSON 格式的角色数据
            return new JsonResponse([
                'roles' => $roleData,
                'permissions' => $permissionData
            ]);
        }
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/_role_list_inner.html.twig', [
            'roles' => $roleData,
            'permissions' => $permissionData
        ]);
    }

    #[Route('/role/create', name: 'admin_role_create', methods: ['POST'])]
    #[IsGranted('SUPER_ADMIN')]
    public function createRole(Request $request, string $adminLoginPath, RoleRepository $roleRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // 检查是否是 AJAX 请求
        if ($request->headers->get('X-Requested-With') !== 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => false,
                'error' => '只允许 AJAX 请求'
            ], 400);
        }
        
        try {
            // 获取请求数据
            $data = json_decode($request->getContent(), true);
            
            // 类型映射
            $typeMapping = [
                '管理员后台权限' => 'admin',
                '供应商后台权限' => 'supplier'
            ];
            
            // 创建新角色
            $newRole = new Role();
            
            // 设置基本信息
            if (!isset($data['name']) || empty($data['name'])) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '角色名称不能为空'
                ], 400);
            }
            
            // 检查角色名称唯一性（转换为大写后检查）
            $roleName = strtoupper($data['name']);
            $existingRole = $roleRepository->findOneBy(['name' => $roleName]);
            if ($existingRole) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '该角色名称已被使用'
                ], 400);
            }
            $newRole->setName($roleName);
            
            if (isset($data['description'])) {
                $newRole->setDescription($data['description']);
            }
            
            if (isset($data['type'])) {
                // 转换类型
                $type = isset($typeMapping[$data['type']]) ? $typeMapping[$data['type']] : $data['type'];
                $newRole->setType($type);
            } else {
                $newRole->setType('admin'); // 默认为管理员类型
            }
            
            // 保存到数据库
            $entityManager->persist($newRole);
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => '角色创建成功'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '创建过程中发生错误: ' . $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/role/delete/{id}', name: 'admin_role_delete', methods: ['POST'])]
    #[IsGranted('SUPER_ADMIN')]
    public function deleteRole(int $id, Request $request, string $adminLoginPath, RoleRepository $roleRepository, AdminRepository $adminRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // 检查是否是 AJAX 请求
        if ($request->headers->get('X-Requested-With') !== 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => false,
                'error' => '只允许 AJAX 请求'
            ], 400);
        }
        
        // 获取要删除的角色
        $roleToDelete = $roleRepository->find($id);
        if (!$roleToDelete) {
            return new JsonResponse([
                'success' => false,
                'error' => '角色不存在'
            ], 404);
        }
        
        // 检查是否是超级管理员角色，禁止删除
        if ($roleToDelete->getName() === 'ROLE_SUPER_ADMIN') {
            return new JsonResponse([
                'success' => false,
                'error' => '无法删除超级管理员角色'
            ], 403);
        }
        
        try {
            // 获取所有管理员
            $admins = $adminRepository->findAll();
            
            // 获取要删除角色的名称
            $roleName = $roleToDelete->getName();
            
            // 遍历所有管理员，从他们的角色中移除要删除的角色
            foreach ($admins as $admin) {
                
                // 如果是type为admin的角色，还需要从管理员的roles字段中移除
                if ($roleToDelete->getType() === 'admin') {
                    // 获取管理员当前的角色数组
                    $currentRoles = $admin->getRoles();
                    
                    // 从角色数组中移除要删除的角色
                    $newRoles = array_filter($currentRoles, function($role) use ($roleName) {
                        return $role !== $roleName;
                    });
                    
                    // 如果角色数组发生了变化，更新管理员的角色
                    if (count($newRoles) !== count($currentRoles)) {
                        $admin->setRoles(array_values($newRoles));
                        $entityManager->persist($admin);
                    }
                }
            }
            
            // 删除角色
            $entityManager->remove($roleToDelete);
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => '角色删除成功'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '删除过程中发生错误: ' . $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/promotion/menu/upload-image', name: 'admin_promotion_menu_upload_image', methods: ['POST'])]
    public function uploadPromotionMenuImage(Request $request, EntityManagerInterface $entityManager, string $adminLoginPath): JsonResponse
    {
        try {
            // 获取上传的文件
            $uploadedFile = $request->files->get('image');
            
            if (!$uploadedFile || !$uploadedFile->isValid()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '上传的文件无效'
                ], 400);
            }
            
            // 验证文件类型
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '只允许上传 JPG、PNG、GIF、WEBP 格式的图片文件'
                ], 400);
            }
            
            // 验证文件大小（限制为5MB）
            if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '文件大小不能超过5MB'
                ], 400);
            }
            
            // 创建七牛云上传服务
            $qiniuService = new QiniuUploadService();
            
            // 生成文件名（使用时间戳+随机数）
            $extension = $uploadedFile->getClientOriginalExtension();
            $fileName = 'promotion_menu_' . time() . '_' . uniqid() . '.' . $extension;
            
            // 上传到七牛云
            $uploadResult = $qiniuService->uploadFile($uploadedFile->getPathname(), $fileName);
            
            if (!$uploadResult['success']) {
                return new JsonResponse([
                    'success' => false,
                    'error' => $uploadResult['error']
                ], 500);
            }
            
            // 返回成功响应（只返回key，前端存储key，每次显示时由后端生成带签名的URL）
            return new JsonResponse([
                'success' => true,
                'url' => $uploadResult['key'],  // 存储key而不是完整URL
                'key' => $uploadResult['key'],
                'previewUrl' => $uploadResult['url'],  // 用于立即预览的临时URL
                'message' => '图片上传成功'
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '上传过程中发生错误: ' . $e->getMessage()
            ], 500);
        }
    }
}