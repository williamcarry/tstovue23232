<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
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

#[Route('/admin{adminLoginPath}', requirements: ['adminLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('ADMIN_ACTIVE')]
class MemberController extends AbstractController
{
    #[Route('/member/list/tab', name: 'admin_member_list_tab')]
    public function memberListTab(string $adminLoginPath, CustomerRepository $customerRepository, Request $request): Response
    {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_MEMBER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_MEMBER')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 获取分页参数
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        
        // 获取搜索参数
        $searchParams = [
            'keyword' => $request->query->get('keyword', ''),
        ];
        
        // 调用Repository的分页查询方法
        $result = $customerRepository->findPaginated($searchParams, $page, $limit);
        
        // 检查是否是 AJAX 请求
        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            // 返回 JSON 格式的会员数据
            return new JsonResponse([
                'success' => true,
                'data' => $this->formatMemberData($result['data']),
                'pagination' => [
                    'currentPage' => $result['page'],
                    'totalPages' => $result['totalPages'],
                    'totalItems' => $result['total'],
                    'itemsPerPage' => $result['limit'],
                ]
            ]);
        }
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/_member_list_inner.html.twig', [
            'members' => $this->formatMemberData($result['data']),
            'pagination' => [
                'currentPage' => $result['page'],
                'totalPages' => $result['totalPages'],
                'totalItems' => $result['total'],
                'itemsPerPage' => $result['limit'],
            ]
        ]);
    }

    #[Route('/member/create/tab', name: 'admin_member_create_tab')]
    public function memberCreateTab(string $adminLoginPath): Response
    {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_MEMBER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_MEMBER')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/_member_create_inner.html.twig');
    }

    #[Route('/member/create', name: 'admin_member_create', methods: ['POST'])]
    public function createMember(
        Request $request, 
        string $adminLoginPath, 
        CustomerRepository $customerRepository, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_MEMBER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_MEMBER')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
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
            
            // 验证用户名格式：必须是英文字母、数字和下划线的组合，且不能以数字和_开头
            $username = $data['username'] ?? '';
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $username)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '用户名必须是英文字母、数字和下划线的组合，且不能以数字或下划线开头'
                ], 400);
            }
            
            // 验证密码长度：必须8位数以上
            $password = $data['password'] ?? '';
            if (strlen($password) < 8) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '密码长度必须至少8位'
                ], 400);
            }
            
            // 检查邮箱唯一性
            $existingMember = $customerRepository->findOneBy(['email' => $data['email']]);
            if ($existingMember) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '该邮箱已被使用'
                ], 400);
            }
            
            // 检查用户名唯一性
            $existingMember = $customerRepository->findOneBy(['username' => $data['username']]);
            if ($existingMember) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '该用户名已被使用'
                ], 400);
            }
            
            // 检查手机号唯一性（如果提供了手机号）
            if (!empty($data['mobile'])) {
                $existingMember = $customerRepository->findOneBy(['mobile' => $data['mobile']]);
                if ($existingMember) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => '该手机号已被使用'
                    ], 400);
                }
            }
            
            // 创建新会员
            $member = new Customer();
            
            // 基本信息
            $member->setUsername($data['username']);
            $member->setEmail($data['email']);
            $member->setPassword($passwordHasher->hashPassword($member, $data['password']));
            $member->setNickname($data['nickname'] ?? null);
            $member->setRealName($data['realName'] ?? null);
            $member->setMobile($data['mobile'] ?? null);
            $member->setGender($data['gender'] ?? 0);
            $member->setAvatar($data['avatar'] ?? null);
            $member->setIndividualIdCard($data['individualIdCard'] ?? null);
            $member->setIndividualIdFront($data['individualIdFront'] ?? null);
            $member->setIndividualIdBack($data['individualIdBack'] ?? null);
            $member->setParentId($data['parentId'] ?? 0);
            
            // 设置默认状态 - 后台创建的会员默认激活并且已实名
            $member->setIsActive(true);
            $member->setIsVerified(true);
            
            // 保存到数据库
            $entityManager->persist($member);
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => '会员创建成功',
                'member' => $this->formatMemberData([$member])[0]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '创建会员失败: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/member/{id}/edit/tab', name: 'admin_member_edit_tab')]
    public function memberEditTab(int $id, string $adminLoginPath, CustomerRepository $customerRepository): Response
    {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_MEMBER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_MEMBER')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        $member = $customerRepository->find($id);
        
        if (!$member) {
            throw $this->createNotFoundException('会员不存在');
        }
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/_member_edit_inner.html.twig', [
            'member' => $this->formatMemberData([$member])[0]
        ]);
    }

    #[Route('/member/{id}/update', name: 'admin_member_update', methods: ['POST'])]
    public function updateMember(
        int $id,
        Request $request, 
        string $adminLoginPath, 
        CustomerRepository $customerRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_MEMBER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_MEMBER')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 检查是否是 AJAX 请求
        if ($request->headers->get('X-Requested-With') !== 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => false,
                'error' => '只允许 AJAX 请求'
            ], 400);
        }
        
        try {
            $member = $customerRepository->find($id);
            
            if (!$member) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '会员不存在'
                ], 404);
            }
            
            // 获取请求数据
            $data = json_decode($request->getContent(), true);
            
            // 检查邮箱唯一性（排除当前会员）
            if (isset($data['email'])) {
                $existingMember = $customerRepository->findOneBy(['email' => $data['email']]);
                if ($existingMember && $existingMember->getId() !== $id) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => '该邮箱已被使用'
                    ], 400);
                }
                $member->setEmail($data['email']);
            }
            
            // 检查用户名唯一性（排除当前会员）
            if (isset($data['username'])) {
                $existingMember = $customerRepository->findOneBy(['username' => $data['username']]);
                if ($existingMember && $existingMember->getId() !== $id) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => '该用户名已被使用'
                    ], 400);
                }
                $member->setUsername($data['username']);
            }
            
            // 检查手机号唯一性（排除当前会员）
            if (isset($data['mobile'])) {
                $existingMember = $customerRepository->findOneBy(['mobile' => $data['mobile']]);
                if ($existingMember && $existingMember->getId() !== $id) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => '该手机号已被使用'
                    ], 400);
                }
                $member->setMobile($data['mobile']);
            }
            
            // 更新密码（如果提供了新密码）
            if (!empty($data['password'])) {
                $member->setPassword($passwordHasher->hashPassword($member, $data['password']));
            }
            
            // 基本信息
            if (isset($data['nickname'])) {
                $member->setNickname($data['nickname']);
            }
            if (isset($data['realName'])) {
                $member->setRealName($data['realName']);
            }
            if (isset($data['gender'])) {
                $member->setGender($data['gender']);
            }
            if (isset($data['avatar'])) {
                $member->setAvatar($data['avatar']);
            }
            if (isset($data['individualIdCard'])) {
                $member->setIndividualIdCard($data['individualIdCard']);
            }
            if (isset($data['individualIdFront'])) {
                $member->setIndividualIdFront($data['individualIdFront']);
            }
            if (isset($data['individualIdBack'])) {
                $member->setIndividualIdBack($data['individualIdBack']);
            }
            if (isset($data['parentId'])) {
                $member->setParentId($data['parentId']);
            }
            if (isset($data['isActive'])) {
                $member->setIsActive($data['isActive']);
            }
            if (isset($data['isVerified'])) {
                $member->setIsVerified($data['isVerified']);
            }
            
            // 保存到数据库
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => '会员更新成功',
                'member' => $this->formatMemberData([$member])[0]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '更新会员失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 上传文件到七牛云
     */
    #[Route('/member/upload-file', name: 'admin_member_upload_file', methods: ['POST'])]
    public function uploadFile(
        Request $request,
        string $adminLoginPath
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_MEMBER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_MEMBER')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        try {
            // 检查是否是 AJAX 请求
            if ($request->headers->get('X-Requested-With') !== 'XMLHttpRequest') {
                return new JsonResponse([
                    'success' => false,
                    'error' => '只允许 AJAX 请求'
                ], 400);
            }
            
            // 获取上传的文件
            $uploadedFile = $request->files->get('file');
            
            if (!$uploadedFile || !$uploadedFile->isValid()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '文件上传失败'
                ], 400);
            }
            
            // 验证文件类型和大小
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '只支持 JPG、PNG、GIF、WEBP 格式的图片'
                ], 400);
            }
            
            // 验证文件大小 (5MB)
            if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '文件大小不能超过 5MB'
                ], 400);
            }
            
            // 创建七牛云服务实例
            $qiniuService = new QiniuUploadService();
            
            // 生成文件名
            $extension = $uploadedFile->getClientOriginalExtension();
            $fileName = 'member_' . time() . '_' . uniqid() . '.' . $extension;
            
            // 上传文件到七牛云
            $uploadResult = $qiniuService->uploadFile($uploadedFile->getPathname(), $fileName);
            
            if (!$uploadResult['success']) {
                return new JsonResponse([
                    'success' => false,
                    'error' => $uploadResult['error']
                ], 500);
            }
            
            // 返回上传结果
            return new JsonResponse([
                'success' => true,
                'url' => $uploadResult['key'],        // 存储到数据库的key
                'key' => $uploadResult['key'],
                'previewUrl' => $uploadResult['url'], // 用于立即预览的带签名URL
                'message' => '文件上传成功'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '文件上传失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取文件签名URL
     */
    #[Route('/member/file/signed-url', name: 'admin_member_file_signed_url', methods: ['POST'])]
    public function getFileSignedUrl(
        Request $request,
        string $adminLoginPath
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_MEMBER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_MEMBER')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        try {
            // 检查是否是 AJAX 请求
            if ($request->headers->get('X-Requested-With') !== 'XMLHttpRequest') {
                return new JsonResponse([
                    'success' => false,
                    'error' => '只允许 AJAX 请求'
                ], 400);
            }
            
            $data = json_decode($request->getContent(), true);
            $key = $data['key'] ?? '';
            
            if (empty($key)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '文件key不能为空'
                ], 400);
            }
            
            // 如果已经是完整URL，直接返回
            if (str_starts_with($key, 'http')) {
                return new JsonResponse([
                    'success' => true,
                    'url' => $key
                ]);
            }
            
            // 创建七牛云服务实例
            $qiniuService = new QiniuUploadService();
            
            // 获取签名URL
            $signedUrl = $qiniuService->getPrivateUrl($key);
            
            return new JsonResponse([
                'success' => true,
                'url' => $signedUrl
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '获取文件URL失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 格式化会员数据以适应前端组件
     */
    private function formatMemberData(array $members): array
    {
        $formattedData = [];
        
        foreach ($members as $member) {
            // 确保传入的是Customer对象
            if (!$member instanceof Customer) {
                continue;
            }
            
            $formattedData[] = [
                'id' => $member->getId(),
                'username' => $member->getUsername(),
                'email' => $member->getEmail(),
                'nickname' => $member->getNickname(),
                'realName' => $member->getRealName(),
                'mobile' => $member->getMobile(),
                'gender' => $member->getGender(),
                'avatar' => $member->getAvatar(),
                'individualIdCard' => $member->getIndividualIdCard(),
                'individualIdFront' => $member->getIndividualIdFront(),
                'individualIdBack' => $member->getIndividualIdBack(),
                'parentId' => $member->getParentId(),
                'isVerified' => $member->isVerified(),
                'isActive' => $member->isActive(),
                'createdAt' => $member->getCreatedAt() ? $member->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'updatedAt' => $member->getUpdatedAt() ? $member->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                'lastLoginAt' => $member->getLastLoginAt() ? $member->getLastLoginAt()->format('Y-m-d H:i:s') : null,
                'vipLevel' => $member->getVipLevel(),
                'vipLevelName' => $member->getVipLevelName(),
                'balance' => $member->getBalance(),
            ];
        }
        
        return $formattedData;
    }
}