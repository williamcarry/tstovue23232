<?php

namespace App\Controller\Admin;

use App\Entity\LogisticsCompany;
use App\Repository\LogisticsCompanyRepository;
use App\Service\LogisticsCompanyService;
use App\Service\QiniuUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin{adminLoginPath}/logistics-company', requirements: ['adminLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('ADMIN_ACTIVE')]
class LogisticsCompanyController extends AbstractController
{
    #[Route('/list/tab', name: 'admin_logistics_company_list_tab')]
    public function logisticsCompanyListTab(
        string $adminLoginPath,
        LogisticsCompanyRepository $logisticsCompanyRepository,
        Request $request
    ): Response {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 获取分页参数
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        
        // 获取搜索参数
        $searchParams = [
            'keyword' => $request->query->get('keyword', ''),
            'isActive' => $request->query->get('isActive', ''),
        ];
        
        // 分页查询物流公司
        $qb = $logisticsCompanyRepository->createQueryBuilder('lc');
        
        // 激活状态筛选
        if (isset($searchParams['isActive']) && $searchParams['isActive'] !== '') {
            $qb->andWhere('lc.isActive = :isActive')
                ->setParameter('isActive', $searchParams['isActive']);
        }
        
        // 关键词搜索
        if (isset($searchParams['keyword']) && !empty($searchParams['keyword'])) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('lc.name', ':keyword'),
                    $qb->expr()->like('lc.nameEn', ':keyword')
                )
            )->setParameter('keyword', '%' . $searchParams['keyword'] . '%');
        }
        
        // 按排序字段和创建时间排序
        $qb->orderBy('lc.sortOrder', 'ASC')
            ->addOrderBy('lc.createdAt', 'DESC');
        
        // 获取总数
        $totalQb = clone $qb;
        $total = count($totalQb->getQuery()->getResult());
        
        // 分页
        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);
        
        $logisticsCompanies = $qb->getQuery()->getResult();
        $totalPages = ceil($total / $limit);
        
        // 转换数据格式
        $companiesData = [];
        foreach ($logisticsCompanies as $company) {
            $companiesData[] = [
                'id' => $company->getId(),
                'name' => $company->getName(),
                'nameEn' => $company->getNameEn(),
                'sortOrder' => $company->getSortOrder(),
                'isActive' => $company->getIsActive(),
                'createdAt' => $company->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $company->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        
        // 检查是否是 AJAX 请求
        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => true,
                'data' => $companiesData,
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => $totalPages,
                    'totalItems' => $total,
                    'itemsPerPage' => $limit,
                ]
            ]);
        }
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/logistics/_list_inner.html.twig', [
            'logisticsCompanies' => $companiesData,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $total,
                'itemsPerPage' => $limit,
            ]
        ]);
    }
    
    #[Route('/create/tab', name: 'admin_logistics_company_create_tab')]
    public function logisticsCompanyCreateTab(string $adminLoginPath): Response
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/logistics/_create_inner.html.twig');
    }
    
    #[Route('/create', name: 'admin_logistics_company_create', methods: ['POST'])]
    public function createLogisticsCompany(
        Request $request,
        string $adminLoginPath,
        LogisticsCompanyService $logisticsCompanyService
    ): JsonResponse {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
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
            
            // 验证必填字段
            if (!isset($data['name']) || empty($data['name'])) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '物流公司名称不能为空'
                ], 400);
            }
            
            // 创建新的物流公司
            $company = $logisticsCompanyService->createLogisticsCompany(
                $data['name'],
                $data['nameEn'] ?? '',
                (int)($data['sortOrder'] ?? 0)
            );
            
            // 设置激活状态
            if (isset($data['isActive'])) {
                $company->setIsActive((bool)$data['isActive']);
            }
            
            return new JsonResponse([
                'success' => true,
                'message' => '物流公司创建成功',
                'data' => [
                    'id' => $company->getId(),
                    'name' => $company->getName(),
                    'nameEn' => $company->getNameEn(),
                    'sortOrder' => $company->getSortOrder(),
                    'isActive' => $company->getIsActive(),
                    'createdAt' => $company->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $company->getUpdatedAt()->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '创建物流公司失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/{id}/edit/tab', name: 'admin_logistics_company_edit_tab')]
    public function logisticsCompanyEditTab(
        int $id,
        Request $request,
        string $adminLoginPath,
        LogisticsCompanyRepository $logisticsCompanyRepository
    ): Response {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            // 检查是否是 AJAX 请求，如果是则返回 JSON 错误响应
            if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Access Denied.'
                ], 403);
            }
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        $company = $logisticsCompanyRepository->find($id);
        
        if (!$company) {
            // 检查是否是 AJAX 请求，如果是则返回 JSON 错误响应
            if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse([
                    'success' => false,
                    'error' => '物流公司不存在'
                ], 404);
            }
            throw $this->createNotFoundException('物流公司不存在');
        }
        
        // 转换数据格式
        $companyData = [
            'id' => $company->getId(),
            'name' => $company->getName(),
            'nameEn' => $company->getNameEn(),
            'sortOrder' => $company->getSortOrder(),
            'isActive' => $company->getIsActive(),
        ];
        
        // 检查是否是 AJAX 请求，如果是则返回 JSON 数据
        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => true,
                'data' => $companyData
            ]);
        }
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/logistics/_edit_inner.html.twig', [
            'adminLoginPath' => $adminLoginPath,
            'logisticsCompany' => $companyData
        ]);
    }
    
    #[Route('/{id}/update', name: 'admin_logistics_company_update', methods: ['POST'])]
    public function updateLogisticsCompany(
        int $id,
        Request $request,
        string $adminLoginPath,
        LogisticsCompanyService $logisticsCompanyService,
        LogisticsCompanyRepository $logisticsCompanyRepository
    ): JsonResponse {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
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
            // 查找物流公司
            $company = $logisticsCompanyRepository->find($id);
            if (!$company) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '物流公司不存在'
                ], 404);
            }
            
            // 获取请求数据
            $data = json_decode($request->getContent(), true);
            
            // 验证必填字段
            if (!isset($data['name']) || empty($data['name'])) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '物流公司名称不能为空'
                ], 400);
            }
            
            // 更新物流公司信息
            $updatedCompany = $logisticsCompanyService->updateLogisticsCompany($id, $data);
            
            if (!$updatedCompany) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '更新物流公司失败'
                ], 500);
            }
            
            return new JsonResponse([
                'success' => true,
                'message' => '物流公司更新成功',
                'data' => [
                    'id' => $updatedCompany->getId(),
                    'name' => $updatedCompany->getName(),
                    'nameEn' => $updatedCompany->getNameEn(),
                    'sortOrder' => $updatedCompany->getSortOrder(),
                    'isActive' => $updatedCompany->getIsActive(),
                    'createdAt' => $updatedCompany->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $updatedCompany->getUpdatedAt()->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '更新物流公司失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/{id}/delete', name: 'admin_logistics_company_delete', methods: ['POST'])]
    public function deleteLogisticsCompany(
        int $id,
        Request $request,
        string $adminLoginPath,
        LogisticsCompanyService $logisticsCompanyService
    ): JsonResponse {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
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
            // 删除物流公司
            $result = $logisticsCompanyService->deleteLogisticsCompany($id);
            
            if (!$result) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '物流公司不存在或删除失败'
                ], 404);
            }
            
            return new JsonResponse([
                'success' => true,
                'message' => '物流公司删除成功'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '删除物流公司失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 上传文件到七牛云
     */
    #[Route('/upload-file', name: 'admin_logistics_company_upload_file', methods: ['POST'])]
    public function uploadFile(
        Request $request,
        string $adminLoginPath
    ): JsonResponse {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
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
            $fileName = 'logistics_company_' . time() . '_' . uniqid() . '.' . $extension;
            
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
    #[Route('/file/signed-url', name: 'admin_logistics_company_file_signed_url', methods: ['POST'])]
    public function getFileSignedUrl(
        Request $request,
        string $adminLoginPath
    ): JsonResponse {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
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
}