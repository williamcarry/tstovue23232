<?php

namespace App\Controller\Admin;

use App\Entity\Supplier;
use App\Repository\SupplierRepository;
use App\Service\CommissionService;
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
class SupplierController extends AbstractController
{
    private $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }
    #[Route('/supplier/list/tab', name: 'admin_supplier_list_tab')]
    public function supplierListTab(string $adminLoginPath, SupplierRepository $supplierRepository, Request $request): Response
    {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SUPPLIER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SUPPLIER')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        // 获取分页参数
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        
        // 获取搜索参数
        $searchParams = [
            'keyword' => $request->query->get('keyword', ''),
            'supplierType' => $request->query->get('supplierType', ''),
            'auditStatus' => $request->query->get('auditStatus', ''),
            'isActive' => $request->query->get('isActive', ''),
        ];
        
        // 调试信息
        error_log('=== 供应商列表查询参数 ===');
        error_log('Page: ' . $page);
        error_log('Limit: ' . $limit);
        error_log('Search Params: ' . json_encode($searchParams));
        
        // 分页查询供应商
        $result = $supplierRepository->findPaginated($searchParams, $page, $limit);
        
        // 调试信息
        error_log('Result total: ' . $result['total']);
        error_log('Result totalPages: ' . $result['totalPages']);
        error_log('Result data count: ' . count($result['data']));
        error_log('========================');
        
        // 检查是否是 AJAX 请求
        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            // 返回 JSON 格式的供应商数据
            return new JsonResponse([
                'success' => true,
                'data' => $this->formatSupplierData($result['data']),
                'pagination' => [
                    'currentPage' => $result['page'],
                    'totalPages' => $result['totalPages'],
                    'totalItems' => $result['total'],
                    'itemsPerPage' => $result['limit'],
                ]
            ]);
        }
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/_supplier_list_inner.html.twig', [
            'suppliers' => $this->formatSupplierData($result['data']),
            'pagination' => [
                'currentPage' => $result['page'],
                'totalPages' => $result['totalPages'],
                'totalItems' => $result['total'],
                'itemsPerPage' => $result['limit'],
            ]
        ]);
    }

    #[Route('/supplier/create/tab', name: 'admin_supplier_create_tab')]
    public function supplierCreateTab(string $adminLoginPath): Response
    {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SUPPLIER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SUPPLIER')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/_supplier_create_inner.html.twig');
    }

    #[Route('/supplier/create', name: 'admin_supplier_create', methods: ['POST'])]
    public function createSupplier(
        Request $request, 
        string $adminLoginPath, 
        SupplierRepository $supplierRepository, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SUPPLIER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SUPPLIER')) {
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
            
            // 验证用户名格式：必须是英文字母数字和_组合，不能以数字和_开头
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
            $existingSupplier = $supplierRepository->findOneBy(['email' => $data['email']]);
            if ($existingSupplier) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '该邮箱已被使用'
                ], 400);
            }
            
            // 检查用户名唯一性
            $existingSupplier = $supplierRepository->findOneBy(['username' => $data['username']]);
            if ($existingSupplier) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '该用户名已被使用'
                ], 400);
            }
            
            // 创建新供应商
            $supplier = new Supplier();
            
            // 基本信息
            $supplier->setUsername($data['username']);
            $supplier->setEmail($data['email']);
            $supplier->setPassword($passwordHasher->hashPassword($supplier, $data['password']));
            $supplier->setContactPerson($data['contactPerson']);
            $supplier->setContactPhone($data['contactPhone']);
            $supplier->setContactAddress($data['contactAddress'] ?? null);
            
            // 设置默认角色
            $supplier->setRoles(['ROLE_SUPPLIER_SUPER']);
            
            // 供应商类型
            $supplier->setSupplierType($data['supplierType']);
            
            // 根据供应商类型设置相应信息
            if ($data['supplierType'] === 'company') {
                $supplier->setCompanyName($data['companyName']);
                $supplier->setCompanyType($data['companyType'] ?? null);
                $supplier->setBusinessLicenseNumber($data['businessLicenseNumber'] ?? null);
                $supplier->setBusinessLicenseImage($data['businessLicenseImage'] ?? null);
                $supplier->setLegalPersonName($data['legalPersonName'] ?? null);
                $supplier->setLegalPersonIdCard($data['legalPersonIdCard'] ?? null);
                $supplier->setLegalPersonIdFront($data['legalPersonIdFront'] ?? null);
                $supplier->setLegalPersonIdBack($data['legalPersonIdBack'] ?? null);
            } else {
                $supplier->setIndividualName($data['individualName'] ?? null);
                $supplier->setIndividualIdCard($data['individualIdCard'] ?? null);
                $supplier->setIndividualIdFront($data['individualIdFront'] ?? null);
                $supplier->setIndividualIdBack($data['individualIdBack'] ?? null);
            }
            
            // 业务信息
            $supplier->setMainCategory($data['mainCategory'] ?? null);
            $supplier->setHasCrossBorderExperience(isset($data['hasCrossBorderExperience']) && $data['hasCrossBorderExperience']);
            $supplier->setAnnualSalesVolume($data['annualSalesVolume'] ?? null);
            $supplier->setWarehouseAddress($data['warehouseAddress'] ?? null);
            
            // 设置默认状态 - 后台创建的供应商默认激活并审核通过
            $supplier->setIsActive(true);
            $supplier->setIsVerified(false);
            $supplier->setAuditStatus('approved'); // 后台创建默认审核通过
            
            // 保存到数据库
            $entityManager->persist($supplier);
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => '供应商创建成功',
                'supplier' => $this->formatSupplierData([$supplier])[0]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '创建供应商失败: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/supplier/{id}/edit/tab', name: 'admin_supplier_edit_tab')]
    public function supplierEditTab(int $id, string $adminLoginPath, SupplierRepository $supplierRepository): Response
    {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SUPPLIER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SUPPLIER')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        $supplier = $supplierRepository->find($id);
        
        if (!$supplier) {
            throw $this->createNotFoundException('供应商不存在');
        }
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/_supplier_edit_inner.html.twig', [
            'supplier' => $this->formatSupplierData([$supplier])[0]
        ]);
    }

    #[Route('/supplier/{id}/update', name: 'admin_supplier_update', methods: ['POST'])]
    public function updateSupplier(
        int $id,
        Request $request, 
        string $adminLoginPath, 
        SupplierRepository $supplierRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SUPPLIER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SUPPLIER')) {
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
            $supplier = $supplierRepository->find($id);
            
            if (!$supplier) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '供应商不存在'
                ], 404);
            }
            
            // 获取请求数据
            $data = json_decode($request->getContent(), true);
            
            // 检查邮箱唯一性（排除当前供应商）
            if (isset($data['email'])) {
                $existingSupplier = $supplierRepository->findOneBy(['email' => $data['email']]);
                if ($existingSupplier && $existingSupplier->getId() !== $id) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => '该邮箱已被使用'
                    ], 400);
                }
                $supplier->setEmail($data['email']);
            }
            
            // 检查用户名唯一性（排除当前供应商）
            if (isset($data['username'])) {
                $existingSupplier = $supplierRepository->findOneBy(['username' => $data['username']]);
                if ($existingSupplier && $existingSupplier->getId() !== $id) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => '该用户名已被使用'
                    ], 400);
                }
                $supplier->setUsername($data['username']);
            }
            
            // 更新密码（如果提供了新密码）
            if (!empty($data['password'])) {
                $supplier->setPassword($passwordHasher->hashPassword($supplier, $data['password']));
            }
            
            // 基本信息
            if (isset($data['contactPerson'])) {
                $supplier->setContactPerson($data['contactPerson']);
            }
            if (isset($data['contactPhone'])) {
                $supplier->setContactPhone($data['contactPhone']);
            }
            if (isset($data['contactAddress'])) {
                $supplier->setContactAddress($data['contactAddress']);
            }
            
            // 供应商类型
            if (isset($data['supplierType'])) {
                $supplier->setSupplierType($data['supplierType']);
            }
            
            // 根据供应商类型更新相应信息
            if (isset($data['supplierType']) && $data['supplierType'] === 'company') {
                if (isset($data['companyName'])) {
                    $supplier->setCompanyName($data['companyName']);
                }
                if (isset($data['companyType'])) {
                    $supplier->setCompanyType($data['companyType']);
                }
                if (isset($data['businessLicenseNumber'])) {
                    $supplier->setBusinessLicenseNumber($data['businessLicenseNumber']);
                }
                if (isset($data['businessLicenseImage'])) {
                    $supplier->setBusinessLicenseImage($data['businessLicenseImage']);
                }
                if (isset($data['legalPersonName'])) {
                    $supplier->setLegalPersonName($data['legalPersonName']);
                }
                if (isset($data['legalPersonIdCard'])) {
                    $supplier->setLegalPersonIdCard($data['legalPersonIdCard']);
                }
                if (isset($data['legalPersonIdFront'])) {
                    $supplier->setLegalPersonIdFront($data['legalPersonIdFront']);
                }
                if (isset($data['legalPersonIdBack'])) {
                    $supplier->setLegalPersonIdBack($data['legalPersonIdBack']);
                }
            } elseif (isset($data['supplierType']) && $data['supplierType'] === 'individual') {
                if (isset($data['individualName'])) {
                    $supplier->setIndividualName($data['individualName']);
                }
                if (isset($data['individualIdCard'])) {
                    $supplier->setIndividualIdCard($data['individualIdCard']);
                }
                if (isset($data['individualIdFront'])) {
                    $supplier->setIndividualIdFront($data['individualIdFront']);
                }
                if (isset($data['individualIdBack'])) {
                    $supplier->setIndividualIdBack($data['individualIdBack']);
                }
            }
            
            // 业务信息
            if (isset($data['mainCategory'])) {
                $supplier->setMainCategory($data['mainCategory']);
            }
            if (isset($data['hasCrossBorderExperience'])) {
                $supplier->setHasCrossBorderExperience($data['hasCrossBorderExperience']);
            }
            if (isset($data['annualSalesVolume'])) {
                $supplier->setAnnualSalesVolume($data['annualSalesVolume']);
            }
            if (isset($data['warehouseAddress'])) {
                $supplier->setWarehouseAddress($data['warehouseAddress']);
            }
            
            // 状态信息
            if (isset($data['isActive'])) {
                $supplier->setIsActive($data['isActive']);
            }
            if (isset($data['isVerified'])) {
                $supplier->setIsVerified($data['isVerified']);
            }
            if (isset($data['auditStatus'])) {
                // 如果审核状态发生变化，需要更新审核人和审核时间
                if ($data['auditStatus'] !== $supplier->getAuditStatus()) {
                    // 获取当前管理员信息
                    $admin = $this->getUser();
                    if ($admin instanceof \App\Entity\Admin) {
                        // 设置审核人（使用管理员ID）
                        $supplier->setAuditedBy($admin->getUsername());
                        // 设置审核时间
                        $supplier->setAuditedAt(new \DateTime());
                    }
                }
                $supplier->setAuditStatus($data['auditStatus']);
            }
            if (isset($data['auditRemark'])) {
                $supplier->setAuditRemark($data['auditRemark']);
            }
            
            // 财务信息 - 只有佣金比例可以修改
            if (isset($data['commissionRate'])) {
                // 如果佣金比例为空或"null"字符串，则设置为null
                if (empty($data['commissionRate']) || strtolower($data['commissionRate']) === 'null') {
                    $supplier->setCommissionRate(null);
                } else {
                    // 验证佣金比例格式
                    $commissionRate = floatval($data['commissionRate']);
                    // 限制佣金比例在0-1之间（0%-100%）
                    if ($commissionRate >= 0 && $commissionRate <= 1) {
                        // 格式化为4位小数
                        $supplier->setCommissionRate(number_format($commissionRate, 4, '.', ''));
                    }
                }
            }
            
            // 保存到数据库
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => '供应商更新成功',
                'supplier' => $this->formatSupplierData([$supplier])[0]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '更新供应商失败: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/supplier/{id}/audit', name: 'admin_supplier_audit', methods: ['POST'])]
    public function auditSupplier(
        int $id,
        Request $request, 
        string $adminLoginPath, 
        SupplierRepository $supplierRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SUPPLIER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SUPPLIER')) {
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
            $supplier = $supplierRepository->find($id);
            
            if (!$supplier) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '供应商不存在'
                ], 404);
            }
            
            // 获取请求数据
            $data = json_decode($request->getContent(), true);
            
            // 获取当前管理员ID
            $admin = $this->getUser();
            $adminId = $admin instanceof \App\Entity\Admin ? $admin->getId() : null;
            
            // 根据操作类型处理审核
            switch ($data['action']) {
                case 'approve':
                    $supplier->approve($adminId, $data['remark'] ?? '审核通过');
                    break;
                case 'reject':
                    $supplier->reject($adminId, $data['remark'] ?? '审核拒绝');
                    break;
                case 'resubmit':
                    $supplier->requireResubmit($adminId, $data['remark'] ?? '需要重新提交');
                    break;
                default:
                    return new JsonResponse([
                        'success' => false,
                        'error' => '无效的审核操作'
                    ], 400);
            }
            
            // 保存到数据库
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => '审核操作成功',
                'supplier' => $this->formatSupplierData([$supplier])[0]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '审核操作失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 上传文件到七牛云
     */
    #[Route('/supplier/upload-file', name: 'admin_supplier_upload_file', methods: ['POST'])]
    public function uploadFile(
        Request $request,
        string $adminLoginPath
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SUPPLIER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SUPPLIER')) {
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
            $fileName = 'supplier_' . time() . '_' . uniqid() . '.' . $extension;
            
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
    #[Route('/supplier/file/signed-url', name: 'admin_supplier_file_signed_url', methods: ['POST'])]
    public function getFileSignedUrl(
        Request $request,
        string $adminLoginPath
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SUPPLIER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SUPPLIER')) {
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
     * 格式化供应商数据以适应前端组件
     */
    private function formatSupplierData(array $suppliers): array
    {
        $formattedData = [];
        
        foreach ($suppliers as $supplier) {
            // 确保传入的是Supplier对象
            if (!$supplier instanceof Supplier) {
                continue;
            }
            
            $formattedData[] = [
                'id' => $supplier->getId(),
                'username' => $supplier->getUsername(),
                'email' => $supplier->getEmail(),
                'roles' => $supplier->getRoles(),
                'supplierType' => $supplier->getSupplierType(),
                'contactPerson' => $supplier->getContactPerson(),
                'contactPhone' => $supplier->getContactPhone(),
                'contactAddress' => $supplier->getContactAddress(),
                'companyName' => $supplier->getCompanyName(),
                'companyType' => $supplier->getCompanyType(),
                'businessLicenseNumber' => $supplier->getBusinessLicenseNumber(),
                'businessLicenseImage' => $supplier->getBusinessLicenseImage(),
                'legalPersonName' => $supplier->getLegalPersonName(),
                'legalPersonIdCard' => $supplier->getLegalPersonIdCard(),
                'legalPersonIdFront' => $supplier->getLegalPersonIdFront(),
                'legalPersonIdBack' => $supplier->getLegalPersonIdBack(),
                'registeredCapital' => $supplier->getRegisteredCapital(),
                'establishmentDate' => $supplier->getEstablishmentDate() ? $supplier->getEstablishmentDate()->format('Y-m-d') : null,
                'businessScope' => $supplier->getBusinessScope(),
                'bankAccountName' => $supplier->getBankAccountName(),
                'bankAccountNumber' => $supplier->getBankAccountNumber(),
                'bankName' => $supplier->getBankName(),
                'bankBranch' => $supplier->getBankBranch(),
                'taxNumber' => $supplier->getTaxNumber(),
                'individualName' => $supplier->getIndividualName(),
                'individualIdCard' => $supplier->getIndividualIdCard(),
                'individualIdFront' => $supplier->getIndividualIdFront(),
                'individualIdBack' => $supplier->getIndividualIdBack(),
                'individualBankAccount' => $supplier->getIndividualBankAccount(),
                'individualBankName' => $supplier->getIndividualBankName(),
                'mainCategory' => $supplier->getMainCategory(),
                'hasCrossBorderExperience' => $supplier->getHasCrossBorderExperience(),
                'annualSalesVolume' => $supplier->getAnnualSalesVolume(),
                'warehouseAddress' => $supplier->getWarehouseAddress(),
                'auditStatus' => $supplier->getAuditStatus(),
                'auditRemark' => $supplier->getAuditRemark(),
                'auditedBy' => $supplier->getAuditedBy(),
                'auditedAt' => $supplier->getAuditedAt() ? $supplier->getAuditedAt()->format('Y-m-d H:i:s') : null,
                'isActive' => $supplier->isActive(),
                'isVerified' => $supplier->getIsVerified(),
                'createdAt' => $supplier->getCreatedAt() ? $supplier->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'updatedAt' => $supplier->getUpdatedAt() ? $supplier->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                'lastLoginAt' => $supplier->getLastLoginAt() ? $supplier->getLastLoginAt()->format('Y-m-d H:i:s') : null,
                'displayName' => $supplier->getDisplayName(),
                // 新增字段
                'balance' => $supplier->getBalance(),
                'balanceFrozen' => $supplier->getBalanceFrozen(),
                'membershipType' => $supplier->getMembershipType(),
                'membershipExpiredAt' => $supplier->getMembershipExpiredAt() ? $supplier->getMembershipExpiredAt()->format('Y-m-d H:i:s') : null,
                'commissionRate' => $supplier->getCommissionRate(),
                'effectiveCommissionRate' => $this->commissionService->getEffectiveCommissionRate($supplier),
                'effectiveCommissionRatePercentage' => $this->commissionService->getEffectiveCommissionRatePercentage($supplier),
            ];
        }
        
        return $formattedData;
    }

    /**
     * 获取供应商详情
     */
    #[Route('/supplier/{id}/detail', name: 'admin_supplier_detail', methods: ['GET'])]
    public function getSupplierDetail(
        int $id,
        string $adminLoginPath, 
        SupplierRepository $supplierRepository
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SUPPLIER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SUPPLIER')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        try {
            $supplier = $supplierRepository->find($id);
            
            if (!$supplier) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '供应商不存在'
                ], 404);
            }
            
            return new JsonResponse([
                'success' => true,
                'data' => $this->formatSupplierData([$supplier])[0]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '获取供应商详情失败: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/supplier/{id}/change-password', name: 'admin_supplier_change_password', methods: ['POST'])]
    public function changeSupplierPassword(
        int $id,
        Request $request, 
        string $adminLoginPath, 
        SupplierRepository $supplierRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        // 检查用户是否具有ROLE_SUPER_ADMIN或ROLE_ADMIN_SUPPLIER角色
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SUPPLIER')) {
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
            $supplier = $supplierRepository->find($id);
            
            if (!$supplier) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '供应商不存在'
                ], 404);
            }
            
            // 获取请求数据
            $data = json_decode($request->getContent(), true);
            
            // 验证新密码
            $newPassword = $data['newPassword'] ?? '';
            if (empty($newPassword)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '新密码不能为空'
                ], 400);
            }
            
            // 验证密码长度：必须8位数以上
            if (strlen($newPassword) < 8) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '密码长度必须至少8位'
                ], 400);
            }
            
            // 更新密码
            $supplier->setPassword($passwordHasher->hashPassword($supplier, $newPassword));
            
            // 保存到数据库
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => '密码修改成功'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '密码修改失败: ' . $e->getMessage()
            ], 500);
        }
    }
}