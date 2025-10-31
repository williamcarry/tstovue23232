<?php

namespace App\Controller\Admin;

use App\Entity\SiteConfig;
use App\Service\SiteConfigService;
use App\Service\QiniuUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin{adminLoginPath}', requirements: ['adminLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('ADMIN_ACTIVE')]
class SiteConfigController extends AbstractController
{
    #[Route('/site-config/list/tab', name: 'admin_site_config_list_tab')]
    public function siteConfigListTab(
        string $adminLoginPath, 
        SiteConfigService $siteConfigService, 
        Request $request
    ): Response {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_SITEMENU')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }
        
        // 获取所有配置项
        $configs = $siteConfigService->getAllConfig();
        
        // 格式化数据
        $configData = [];
        foreach ($configs as $config) {
            $configData[] = [
                'id' => $config->getId(),
                'configKey' => $config->getConfigKey(),
                'configValue' => $config->getConfigValue(),
                'description' => $config->getDescription(),
                'createdAt' => $config->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $config->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        
        // 检查是否是 AJAX 请求
        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            // 返回 JSON 格式的数据
            return new JsonResponse([
                'success' => true,
                'data' => $configData,
                'pagination' => [
                    'currentPage' => 1,
                    'totalPages' => 1,
                    'totalItems' => count($configData),
                    'itemsPerPage' => count($configData),
                ]
            ]);
        }
        
        // 渲染标签页内容而不是完整页面
        return $this->render('admin/_site_config_list_inner.html.twig', [
            'configs' => $configData,
        ]);
    }

    #[Route('/site-config/create', name: 'admin_site_config_create', methods: ['POST'])]
    public function createSiteConfig(
        Request $request, 
        string $adminLoginPath, 
        SiteConfigService $siteConfigService
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
            if (!isset($data['configKey']) || empty($data['configKey'])) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '配置键名不能为空'
                ], 400);
            }
            
            // 验证配置键名唯一性
            $existingConfig = $siteConfigService->getConfigValue($data['configKey']);
            if ($existingConfig !== null) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '该配置键名已存在'
                ], 400);
            }
            
            // 创建新配置项
            $config = $siteConfigService->setConfigValue(
                $data['configKey'],
                $data['configValue'] ?? null,
                $data['description'] ?? null
            );
            
            return new JsonResponse([
                'success' => true,
                'message' => '配置项创建成功',
                'data' => [
                    'id' => $config->getId(),
                    'configKey' => $config->getConfigKey(),
                    'configValue' => $config->getConfigValue(),
                    'description' => $config->getDescription(),
                    'createdAt' => $config->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $config->getUpdatedAt()->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '创建配置项失败: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/site-config/update/{id}', name: 'admin_site_config_update', methods: ['POST'])]
    public function updateSiteConfig(
        int $id,
        Request $request, 
        string $adminLoginPath, 
        SiteConfigService $siteConfigService,
        EntityManagerInterface $entityManager
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
            
            // 查找配置项
            $config = $entityManager->getRepository(SiteConfig::class)->find($id);
            if (!$config) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '配置项不存在'
                ], 404);
            }
            
            // 更新配置项
            if (isset($data['configValue'])) {
                $config->setConfigValue($data['configValue']);
            }
            
            if (isset($data['description'])) {
                $config->setDescription($data['description']);
            }
            
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => '配置项更新成功',
                'data' => [
                    'id' => $config->getId(),
                    'configKey' => $config->getConfigKey(),
                    'configValue' => $config->getConfigValue(),
                    'description' => $config->getDescription(),
                    'createdAt' => $config->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $config->getUpdatedAt()->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '更新配置项失败: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/site-config/delete/{id}', name: 'admin_site_config_delete', methods: ['POST'])]
    public function deleteSiteConfig(
        int $id,
        Request $request, 
        string $adminLoginPath, 
        SiteConfigService $siteConfigService,
        EntityManagerInterface $entityManager
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
            // 查找配置项
            $config = $entityManager->getRepository(SiteConfig::class)->find($id);
            if (!$config) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '配置项不存在'
                ], 404);
            }
            
            // 删除配置项
            $entityManager->remove($config);
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => '配置项删除成功'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '删除配置项失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 上传文件到七牛云
     */
    #[Route('/site-config/upload-file', name: 'admin_site_config_upload_file', methods: ['POST'])]
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
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
            if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '只支持 JPG、PNG、GIF、WEBP、SVG 格式的图片'
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
            $fileName = 'site_config_' . time() . '_' . uniqid() . '.' . $extension;
            
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
    #[Route('/site-config/file/signed-url', name: 'admin_site_config_file_signed_url', methods: ['POST'])]
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