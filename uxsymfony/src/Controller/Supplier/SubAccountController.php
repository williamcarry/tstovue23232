<?php

namespace App\Controller\Supplier;

use App\Entity\Supplier;
use App\Entity\SupplierSubAccount;
// 移除了对SupplierRole的引用
use App\Repository\SupplierSubAccountRepository;
use App\Repository\RoleRepository;
use App\Service\PathConfigService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/supplier{supplierLoginPath}', requirements: ['supplierLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('SUPPLIER_ACTIVE')]
class SubAccountController extends AbstractController
{
    /**
     * 子账号列表页面内容（AJAX加载）
     */
    #[Route('/sub-account/list/tab', name: 'supplier_sub_account_list_tab', methods: ['GET'])]
    #[IsGranted('ROLE_SUPPLIER_SUPER')]
    public function subAccountListTab(string $supplierLoginPath): Response
    {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            throw $this->createNotFoundException('Invalid supplier path');
        }
        
        // 检查用户是否已登录且具有供应商角色
        if (!$this->isGranted('ROLE_SUPPLIER')) {
            return new Response('需要登录才能访问此页面', 401);
        }
        
        // 渲染标签页内容而不是完整页面
        return $this->render('supplier/_sub_account_list_inner.html.twig', [
            'supplierLoginPath' => $supplierLoginPath,
        ]);
    }
    
    /**
     * 获取子账号列表数据（JSON API）
     */
    #[Route('/sub-account/list/data', name: 'supplier_sub_account_list_data', methods: ['GET'])]
    #[IsGranted('ROLE_SUPPLIER_SUPER')]
    public function subAccountListData(
        string $supplierLoginPath,
        SupplierSubAccountRepository $subAccountRepository,
        RoleRepository $roleRepository,
        Request $request
    ): JsonResponse {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            throw $this->createNotFoundException('Invalid supplier path');
        }
        
        // 检查用户是否已登录且具有供应商角色
        if (!$this->isGranted('ROLE_SUPPLIER')) {
            return new JsonResponse(['success' => false, 'message' => '需要登录才能访问此页面'], 401);
        }
        
        // 获取当前供应商
        /** @var Supplier $currentSupplier */
        $currentSupplier = $this->getUser();
        
        // 获取分页参数
        $page = max(1, intval($request->query->get('page', 1)));
        $limit = min(100, max(10, intval($request->query->get('limit', 20)))); // 限制每页显示条数在10-100之间
        
        // 计算偏移量
        $offset = ($page - 1) * $limit;
        
        // 查询条件：只查询当前供应商的子账号
        $criteria = ['supplier' => $currentSupplier];
        
        // 获取总记录数
        $totalItems = $subAccountRepository->count($criteria);
        
        // 获取当前页数据
        $subAccounts = $subAccountRepository->findBy(
            $criteria,
            ['id' => 'ASC'],
            $limit,
            $offset
        );
        
        // 转换数据格式
        $subAccountsData = [];
        foreach ($subAccounts as $subAccount) {
            $subAccountsData[] = [
                'id' => $subAccount->getId(),
                'username' => $subAccount->getUsername(),
                'mark' => $subAccount->getMark(),
                'email' => $subAccount->getEmail(),
                'isActive' => $subAccount->isActive(),
                'createdAt' => $subAccount->getCreatedAt() ? $subAccount->getCreatedAt()->format('Y-m-d H:i:s') : '',
                'lastLoginAt' => $subAccount->getLastLoginAt() ? $subAccount->getLastLoginAt()->format('Y-m-d H:i:s') : '',
                'roles' => $subAccount->getRoles() // 直接使用子账号的roles字段
            ];
        }
        
        // 获取可用的供应商角色
        $roles = $roleRepository->findBy(['type' => 'supplier']);
        $rolesData = [];
        foreach ($roles as $role) {
            $rolesData[] = [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'description' => $role->getDescription()
            ];
        }
        
        // 计算总页数
        $totalPages = ceil($totalItems / $limit);
        
        return new JsonResponse([
            'success' => true,
            'data' => [
                'subAccounts' => $subAccountsData,
                'roles' => $rolesData,
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => $totalPages,
                    'totalItems' => $totalItems,
                    'itemsPerPage' => $limit
                ]
            ]
        ]);
    }

    /**
     * 创建子账号
     */
    #[Route('/sub-account/create', name: 'supplier_sub_account_create', methods: ['POST'])]
    #[IsGranted('ROLE_SUPPLIER_SUPER')]
    public function createSubAccount(
        string $supplierLoginPath,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SupplierSubAccountRepository $subAccountRepository,
        RoleRepository $roleRepository
    ): JsonResponse {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            throw $this->createNotFoundException('Invalid supplier path');
        }
        
        // 检查用户是否已登录且具有供应商角色
        if (!$this->isGranted('ROLE_SUPPLIER')) {
            return new JsonResponse(['success' => false, 'error' => '需要登录才能访问此页面'], 401);
        }
        
        // 获取当前供应商
        /** @var Supplier $currentSupplier */
        $currentSupplier = $this->getUser();
        
        // 获取请求数据
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['success' => false, 'error' => '无效的请求数据'], 400);
        }
        
        // 验证必填字段
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        $mark = trim($data['mark'] ?? '');
        $isActive = boolval($data['isActive'] ?? true);
        $roleIds = $data['roles'] ?? []; // 获取选中的角色ID数组
        
        if (empty($username)) {
            return new JsonResponse(['success' => false, 'error' => '用户名不能为空'], 400);
        }
        
        if (empty($email)) {
            return new JsonResponse(['success' => false, 'error' => '邮箱不能为空'], 400);
        }
        
        if (empty($password)) {
            return new JsonResponse(['success' => false, 'error' => '密码不能为空'], 400);
        }
        
        // 验证密码长度
        if (strlen($password) < 8) {
            return new JsonResponse(['success' => false, 'error' => '密码必须至少包含8个字符'], 400);
        }
        
        // 检查用户名是否已存在
        $existingSubAccount = $subAccountRepository->findOneBy(['username' => $username]);
        if ($existingSubAccount) {
            return new JsonResponse(['success' => false, 'error' => '用户名已存在'], 400);
        }
        
        // 检查邮箱是否已存在
        $existingSubAccount = $subAccountRepository->findOneBy(['email' => $email]);
        if ($existingSubAccount) {
            return new JsonResponse(['success' => false, 'error' => '邮箱已存在'], 400);
        }
        
        // 创建新子账号
        $subAccount = new SupplierSubAccount();
        $subAccount->setUsername($username);
        $subAccount->setMark($mark);
        $subAccount->setEmail($email);
        $subAccount->setPassword($passwordHasher->hashPassword($subAccount, $password));
        $subAccount->setIsActive($isActive);
        $subAccount->setSupplier($currentSupplier);
        
        // 设置角色
        $roles = [];
        foreach ($roleIds as $roleId) {
            $role = $roleRepository->find($roleId);
            if ($role && $role->getType() === 'supplier') {
                $roles[] = $role->getName();
            }
        }
        // 确保子账号至少有基本角色
        $roles = array_unique(array_merge($roles, ['ROLE_SUPPLIER', 'ROLE_SUPPLIER_SUB_ACCOUNT']));
        $subAccount->setRoles($roles);
        
        // 保存到数据库
        $entityManager->persist($subAccount);
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '子账号创建成功',
            'data' => [
                'id' => $subAccount->getId()
            ]
        ]);
    }
    
    /**
     * 更新子账号
     */
    #[Route('/sub-account/update/{id}', name: 'supplier_sub_account_update', methods: ['POST'])]
    #[IsGranted('ROLE_SUPPLIER_SUPER')]
    public function updateSubAccount(
        int $id,
        string $supplierLoginPath,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SupplierSubAccountRepository $subAccountRepository,
        RoleRepository $roleRepository
    ): JsonResponse {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            throw $this->createNotFoundException('Invalid supplier path');
        }
        
        // 检查用户是否已登录且具有供应商角色
        if (!$this->isGranted('ROLE_SUPPLIER')) {
            return new JsonResponse(['success' => false, 'error' => '需要登录才能访问此页面'], 401);
        }
        
        // 获取当前供应商
        /** @var Supplier $currentSupplier */
        $currentSupplier = $this->getUser();
        
        // 查找子账号
        $subAccount = $subAccountRepository->find($id);
        
        if (!$subAccount) {
            return new JsonResponse(['success' => false, 'error' => '子账号不存在'], 404);
        }
        
        // 验证子账号是否属于当前供应商
        if ($subAccount->getSupplier()->getId() !== $currentSupplier->getId()) {
            return new JsonResponse(['success' => false, 'error' => '无权操作此子账号'], 403);
        }
        
        // 获取请求数据
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['success' => false, 'error' => '无效的请求数据'], 400);
        }
        
        // 验证字段
        $mark = trim($data['mark'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = isset($data['password']) ? trim($data['password']) : '';
        $confirmPassword = isset($data['confirmPassword']) ? trim($data['confirmPassword']) : '';
        $isActive = boolval($data['isActive'] ?? true);
        $roleIds = $data['roles'] ?? []; // 获取选中的角色ID数组
        
        if (empty($email)) {
            return new JsonResponse(['success' => false, 'error' => '邮箱不能为空'], 400);
        }
        
        // 验证密码
        if (!empty($password)) {
            if (strlen($password) < 8) {
                return new JsonResponse(['success' => false, 'error' => '密码必须至少包含8个字符'], 400);
            }
            
            if ($password !== $confirmPassword) {
                // dd($password, $confirmPassword);
                return new JsonResponse(['success' => false, 'error' => '两次输入的密码不一致'], 400);
            }
        }
        
        // 检查邮箱是否已存在（排除当前子账号）
        $existingSubAccount = $subAccountRepository->findOneBy(['email' => $email]);
        if ($existingSubAccount && $existingSubAccount->getId() !== $id) {
            return new JsonResponse(['success' => false, 'error' => '邮箱已存在'], 400);
        }
        
        // 更新子账号信息
        $subAccount->setMark($mark);
        $subAccount->setEmail($email);
        $subAccount->setIsActive($isActive);
        
        // 如果提供了密码，则更新密码
        if (!empty($password)) {
            $subAccount->setPassword($passwordHasher->hashPassword($subAccount, $password));
        }
        
        // 更新角色
        $roles = [];
        foreach ($roleIds as $roleId) {
            $role = $roleRepository->find($roleId);
            if ($role && $role->getType() === 'supplier') {
                $roles[] = $role->getName();
            }
        }
        // 确保子账号至少有基本角色
        $roles = array_unique(array_merge($roles, ['ROLE_SUPPLIER', 'ROLE_SUPPLIER_SUB_ACCOUNT']));
        $subAccount->setRoles($roles);
        
        // 保存到数据库
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '子账号更新成功'
        ]);
    }
    
    /**
     * 切换子账号状态
     */
    #[Route('/sub-account/toggle-status/{id}', name: 'supplier_sub_account_toggle_status', methods: ['POST'])]
    #[IsGranted('ROLE_SUPPLIER_SUPER')]
    public function toggleSubAccountStatus(
        int $id,
        string $supplierLoginPath,
        EntityManagerInterface $entityManager,
        SupplierSubAccountRepository $subAccountRepository
    ): JsonResponse {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            throw $this->createNotFoundException('Invalid supplier path');
        }
        
        // 检查用户是否已登录且具有供应商角色
        if (!$this->isGranted('ROLE_SUPPLIER')) {
            return new JsonResponse(['success' => false, 'error' => '需要登录才能访问此页面'], 401);
        }
        
        // 获取当前供应商
        /** @var Supplier $currentSupplier */
        $currentSupplier = $this->getUser();
        
        // 查找子账号
        $subAccount = $subAccountRepository->find($id);
        
        if (!$subAccount) {
            return new JsonResponse(['success' => false, 'error' => '子账号不存在'], 404);
        }
        
        // 验证子账号是否属于当前供应商
        if ($subAccount->getSupplier()->getId() !== $currentSupplier->getId()) {
            return new JsonResponse(['success' => false, 'error' => '无权操作此子账号'], 403);
        }
        
        // 切换状态
        $subAccount->setIsActive(!$subAccount->isActive());
        
        // 保存到数据库
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => $subAccount->isActive() ? '子账号已启用' : '子账号已禁用'
        ]);
    }
    
    /**
     * 删除子账号
     */
    #[Route('/sub-account/delete/{id}', name: 'supplier_sub_account_delete', methods: ['POST'])]
    #[IsGranted('ROLE_SUPPLIER_SUPER')]
    public function deleteSubAccount(
        int $id,
        string $supplierLoginPath,
        EntityManagerInterface $entityManager,
        SupplierSubAccountRepository $subAccountRepository
    ): JsonResponse {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            throw $this->createNotFoundException('Invalid supplier path');
        }
        
        // 检查用户是否已登录且具有供应商角色
        if (!$this->isGranted('ROLE_SUPPLIER')) {
            return new JsonResponse(['success' => false, 'error' => '需要登录才能访问此页面'], 401);
        }
        
        // 获取当前供应商
        /** @var Supplier $currentSupplier */
        $currentSupplier = $this->getUser();
        
        // 查找子账号
        $subAccount = $subAccountRepository->find($id);
        
        if (!$subAccount) {
            return new JsonResponse(['success' => false, 'error' => '子账号不存在'], 404);
        }
        
        // 验证子账号是否属于当前供应商
        if ($subAccount->getSupplier()->getId() !== $currentSupplier->getId()) {
            return new JsonResponse(['success' => false, 'error' => '无权操作此子账号'], 403);
        }
        
        // 删除子账号
        $entityManager->remove($subAccount);
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '子账号删除成功'
        ]);
    }
}