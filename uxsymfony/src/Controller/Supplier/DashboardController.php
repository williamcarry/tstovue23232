<?php

namespace App\Controller\Supplier;

use App\Entity\Supplier;
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

#[Route('/supplier{supplierLoginPath}', requirements: ['supplierLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('SUPPLIER_ACTIVE')]
class DashboardController extends AbstractController
{
    #[Route('/supplier', name: 'supplier_dashboard')]
    #[Route('/supplier/dashboard', name: 'supplier_dashboard_alt')]
    public function index(string $supplierLoginPath)
    {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            // 记录调试信息
            error_log('Path mismatch in index: request=' . $supplierLoginPath . ', config=' . PathConfigService::getSupplierLoginPath());
            throw $this->createNotFoundException('Invalid supplier path');
        }
        
        // 获取当前用户名
        $username = 'Supplier User';
        if ($this->getUser()) {
            $username = $this->getUser()->getUserIdentifier();
        }
        
        return $this->render('supplier/dashboard.html.twig', [
            'dashboard_title' => '供应商后台',
            'supplier_name' => $username,
            'supplierLoginPath' => $supplierLoginPath,
        ]);
    }
    
    /**
     * 获取供应商个人信息数据
     */
    #[Route('/profile/data', name: 'supplier_profile_data', methods: ['GET'])]
    #[IsGranted('ROLE_SUPPLIER_SUPER')]
    public function getProfileData(string $supplierLoginPath): JsonResponse
    {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }
        
        /** @var Supplier $supplier */
        $supplier = $this->getUser();
        
        // 创建七牛云服务实例
        $qiniuService = new QiniuUploadService();
        
        // 格式化数据
        $data = [
            'id' => $supplier->getId(),
            'username' => $supplier->getUsername(),
            'email' => $supplier->getEmail(),
            'supplierType' => $supplier->getSupplierType(),
            'displayName' => $supplier->getSupplierType() === 'company' ? $supplier->getCompanyName() : $supplier->getIndividualName(),
            'createdAt' => $supplier->getCreatedAt() ? $supplier->getCreatedAt()->format('Y-m-d H:i:s') : '',
            'lastLoginAt' => $supplier->getLastLoginAt() ? $supplier->getLastLoginAt()->format('Y-m-d H:i:s') : '',
            'updatedAt' => $supplier->getUpdatedAt() ? $supplier->getUpdatedAt()->format('Y-m-d H:i:s') : '',
            'contactPerson' => $supplier->getContactPerson(),
            'contactPhone' => $supplier->getContactPhone(),
            'contactAddress' => $supplier->getContactAddress(),
            // 公司信息
            'companyName' => $supplier->getCompanyName(),
            'companyType' => $supplier->getCompanyType(),
            'businessLicenseNumber' => $supplier->getBusinessLicenseNumber(),
            'businessLicenseImage' => $supplier->getBusinessLicenseImage(),
            'legalPersonName' => $supplier->getLegalPersonName(),
            'legalPersonIdCard' => $supplier->getLegalPersonIdCard(),
            'legalPersonIdFront' => $supplier->getLegalPersonIdFront(),
            'legalPersonIdBack' => $supplier->getLegalPersonIdBack(),
            'registeredCapital' => $supplier->getRegisteredCapital(),
            'establishmentDate' => $supplier->getEstablishmentDate() ? $supplier->getEstablishmentDate()->format('Y-m-d') : '',
            'businessScope' => $supplier->getBusinessScope(),
            'bankAccountName' => $supplier->getBankAccountName(),
            'bankAccountNumber' => $supplier->getBankAccountNumber(),
            'bankName' => $supplier->getBankName(),
            'bankBranch' => $supplier->getBankBranch(),
            'taxNumber' => $supplier->getTaxNumber(),
            // 个人信息
            'individualName' => $supplier->getIndividualName(),
            'individualIdCard' => $supplier->getIndividualIdCard(),
            'individualIdFront' => $supplier->getIndividualIdFront(),
            'individualIdBack' => $supplier->getIndividualIdBack(),
            'individualBankAccount' => $supplier->getIndividualBankAccount(),
            'individualBankName' => $supplier->getIndividualBankName(),
            // 业务信息
            'mainCategory' => $supplier->getMainCategory(),
            'hasCrossBorderExperience' => $supplier->getHasCrossBorderExperience(),
            'annualSalesVolume' => $supplier->getAnnualSalesVolume(),
            'warehouseAddress' => $supplier->getWarehouseAddress(),
            // 审核信息
            'auditStatus' => $supplier->getAuditStatus(),
            'auditRemark' => $supplier->getAuditRemark(),
        ];
        
        // 为图片字段生成签名URL
        $imageFields = [
            'businessLicenseImage' => 'businessLicenseImageUrl',
            'legalPersonIdFront' => 'legalPersonIdFrontUrl',
            'legalPersonIdBack' => 'legalPersonIdBackUrl',
            'individualIdFront' => 'individualIdFrontUrl',
            'individualIdBack' => 'individualIdBackUrl',
        ];
        
        foreach ($imageFields as $keyField => $urlField) {
            $imageKey = $data[$keyField];
            if ($imageKey) {
                // 处理旧数据（如果存的是完整URL，提取key）
                if (str_starts_with($imageKey, 'http')) {
                    $parts = parse_url($imageKey);
                    $imageKey = ltrim($parts['path'] ?? '', '/');
                    $imageKey = preg_replace('/\?.*$/', '', $imageKey);
                }
                // 生成签名URL
                $data[$urlField] = $qiniuService->getPrivateUrl($imageKey);
            } else {
                $data[$urlField] = '';
            }
        }
        
        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }
    
    /**
     * 更新供应商个人信息
     */
    #[Route('/profile/update', name: 'supplier_profile_update', methods: ['POST'])]
    #[IsGranted('ROLE_SUPPLIER_SUPER')]
    public function updateProfile(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        string $supplierLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid supplier path'], 404);
        }
        
        /** @var Supplier $supplier */
        $supplier = $this->getUser();
        
        // 获取提交的数据
        $data = json_decode($request->getContent(), true);
        
        // 保存原始邮箱
        $originalEmail = $supplier->getEmail();
        
        // 更新可编辑的字段
        if (isset($data['email'])) {
            $supplier->setEmail($data['email']);
        }
        if (isset($data['contactPerson'])) {
            $supplier->setContactPerson($data['contactPerson']);
        }
        if (isset($data['contactPhone'])) {
            $supplier->setContactPhone($data['contactPhone']);
        }
        if (isset($data['contactAddress'])) {
            $supplier->setContactAddress($data['contactAddress']);
        }
        
        // 更新显示名称（无论审核状态如何都可以修改）
        if (isset($data['displayName'])) {
            if ($supplier->getSupplierType() === 'company') {
                $supplier->setCompanyName($data['displayName']);
            } else {
                $supplier->setIndividualName($data['displayName']);
            }
        }
        
        // 更新银行信息
        if (isset($data['bankAccountName'])) {
            $supplier->setBankAccountName($data['bankAccountName']);
        }
        if (isset($data['bankAccountNumber'])) {
            $supplier->setBankAccountNumber($data['bankAccountNumber']);
        }
        if (isset($data['bankName'])) {
            $supplier->setBankName($data['bankName']);
        }
        if (isset($data['bankBranch'])) {
            $supplier->setBankBranch($data['bankBranch']);
        }
        if (isset($data['individualBankAccount'])) {
            $supplier->setIndividualBankAccount($data['individualBankAccount']);
        }
        if (isset($data['individualBankName'])) {
            $supplier->setIndividualBankName($data['individualBankName']);
        }
        
        // 更新业务信息
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
        
        // 如果审核状态不是approved，则允许更新公司或个人信息
        if ($supplier->getAuditStatus() !== 'approved') {
            // 根据供应商类型更新相应信息
            if ($supplier->getSupplierType() === 'company') {
                // 处理显示名称 - 如果提供了新的显示名称，则更新公司名称
                if (isset($data['displayName']) && !empty($data['displayName'])) {
                    $supplier->setCompanyName($data['displayName']);
                }
                
                if (isset($data['companyName'])) {
                    $supplier->setCompanyName($data['companyName']);
                }
                if (isset($data['companyType'])) {
                    $supplier->setCompanyType($data['companyType']);
                }
                if (isset($data['businessLicenseNumber'])) {
                    $supplier->setBusinessLicenseNumber($data['businessLicenseNumber']);
                }
                if (isset($data['legalPersonName'])) {
                    $supplier->setLegalPersonName($data['legalPersonName']);
                }
                if (isset($data['legalPersonIdCard'])) {
                    $supplier->setLegalPersonIdCard($data['legalPersonIdCard']);
                }
                if (isset($data['registeredCapital'])) {
                    $supplier->setRegisteredCapital($data['registeredCapital']);
                }
                if (isset($data['establishmentDate'])) {
                    $supplier->setEstablishmentDate($data['establishmentDate'] ? new \DateTime($data['establishmentDate']) : null);
                }
                if (isset($data['businessScope'])) {
                    $supplier->setBusinessScope($data['businessScope']);
                }
                if (isset($data['taxNumber'])) {
                    $supplier->setTaxNumber($data['taxNumber']);
                }
                // 处理公司证件图片上传 - 使用array_key_exists允许空值
                if (array_key_exists('businessLicenseImage', $data)) {
                    $supplier->setBusinessLicenseImage($data['businessLicenseImage'] ?: null);
                }
                if (array_key_exists('legalPersonIdFront', $data)) {
                    $supplier->setLegalPersonIdFront($data['legalPersonIdFront'] ?: null);
                }
                if (array_key_exists('legalPersonIdBack', $data)) {
                    $supplier->setLegalPersonIdBack($data['legalPersonIdBack'] ?: null);
                }
            } else {
                // 个人供应商
                // 处理显示名称 - 如果提供了新的显示名称，则更新个人姓名
                if (isset($data['displayName']) && !empty($data['displayName'])) {
                    $supplier->setIndividualName($data['displayName']);
                }
                
                if (isset($data['individualName'])) {
                    $supplier->setIndividualName($data['individualName']);
                }
                if (isset($data['individualIdCard'])) {
                    $supplier->setIndividualIdCard($data['individualIdCard']);
                }
                // 处理个人证件图片上传 - 使用array_key_exists允许空值
                if (array_key_exists('individualIdFront', $data)) {
                    $supplier->setIndividualIdFront($data['individualIdFront'] ?: null);
                }
                if (array_key_exists('individualIdBack', $data)) {
                    $supplier->setIndividualIdBack($data['individualIdBack'] ?: null);
                }
            }
        }
        
        // 验证邮箱唯一性（如果邮箱有更改）
        if ($data['email'] !== $originalEmail) {
            $existingSupplier = $entityManager->getRepository(Supplier::class)->findOneBy(['email' => $data['email']]);
            if ($existingSupplier && $existingSupplier->getId() !== $supplier->getId()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '该邮箱已被其他供应商使用'
                ]);
            }
        }
        
        // 验证数据
        $errors = $validator->validate($supplier);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'error' => implode(', ', $errorMessages)
            ]);
        }
        
        // 处理密码修改（不需要当前密码）
        if (!empty($data['newPassword']) && !empty($data['confirmPassword'])) {
            // 验证新密码和确认密码是否一致
            if ($data['newPassword'] !== $data['confirmPassword']) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '新密码和确认密码不一致'
                ]);
            }
            
            // 验证新密码长度（至少8个字符）
            if (strlen($data['newPassword']) < 8) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '新密码至少需要8个字符'
                ]);
            }
            
            // 设置新密码
            $supplier->setPassword($passwordHasher->hashPassword($supplier, $data['newPassword']));
        }
        
        // 如果当前审核状态不是approved，则将审核状态改为pending
        if ($supplier->getAuditStatus() !== 'approved') {
            $supplier->setAuditStatus('pending');
        }
        
        // 保存到数据库
        $supplier->setUpdatedAt(new \DateTime());
        $entityManager->persist($supplier);
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => '个人信息更新成功'
        ]);
    }

    /**
     * 上传文件到七牛云（供应商后台专用）
     */
    #[Route('/upload-file', name: 'supplier_upload_file', methods: ['POST'])]
    public function uploadFile(
        Request $request,
        string $supplierLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse([
                'success' => false,
                'error' => '无效的供应商路径'
            ], 404);
        }
        
        // 检查用户是否已登录
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => '请先登录供应商后台'
            ], 401);
        }
        
        // 检查用户是否是供应商
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER')) {
            return new JsonResponse([
                'success' => false,
                'error' => '没有权限访问此功能'
            ], 403);
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
     * 获取文件签名URL（供应商后台专用）
     */
    #[Route('/file/signed-url', name: 'supplier_file_signed_url', methods: ['POST'])]
    public function getFileSignedUrl(
        Request $request,
        string $supplierLoginPath
    ): JsonResponse {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse([
                'success' => false,
                'error' => '无效的供应商路径'
            ], 404);
        }
        
        // 检查用户是否已登录
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => '请先登录供应商后台'
            ], 401);
        }
        
        // 检查用户是否是供应商
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER')) {
            return new JsonResponse([
                'success' => false,
                'error' => '没有权限访问此功能'
            ], 403);
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

}