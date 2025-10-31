<?php

namespace App\Controller\Supplier;

use App\Entity\Supplier;
use App\Service\PathConfigService;
use App\Service\QiniuUploadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/supplier{supplierLoginPath}', requirements: ['supplierLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('SUPPLIER_ACTIVE')]
class FileController extends AbstractController
{
    /**
     * 通用文件上传接口
     */
    #[Route('/upload-file', name: 'supplier_upload_file', methods: ['POST'])]
    public function uploadFile(Request $request, string $supplierLoginPath): JsonResponse
    {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            return new JsonResponse(['success' => false, 'message' => '未上传文件'], 400);
        }

        // 验证文件大小（最大10MB）
        if ($uploadedFile->getSize() > 10 * 1024 * 1024) {
            return new JsonResponse(['success' => false, 'message' => '文件大小不能超过10MB'], 400);
        }

        try {
            $qiniuService = new QiniuUploadService();
            
            // 获取文件扩展名
            $extension = $uploadedFile->getClientOriginalExtension();
            $originalName = $uploadedFile->getClientOriginalName();
            
            // 生成文件名
            $fileName = 'supplier_' . time() . '_' . uniqid() . '.' . $extension;

            $uploadResult = $qiniuService->uploadFile($uploadedFile->getPathname(), $fileName);

            if ($uploadResult['success']) {
                return new JsonResponse([
                    'success' => true,
                    'url' => $uploadResult['key'],
                    'key' => $uploadResult['key'],
                    'previewUrl' => $uploadResult['url'],
                    'originalName' => $originalName,
                    'message' => '文件上传成功'
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => '上传失败：' . $uploadResult['error']
                ], 500);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => '上传异常：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取七牛云私有文件签名URL
     */
    #[Route('/file/signed-url', name: 'supplier_file_signed_url', methods: ['POST'])]
    public function getSignedUrl(Request $request, string $supplierLoginPath): JsonResponse
    {
        // 权限检查
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_PRODUCT')) {
            return new JsonResponse(['success' => false, 'message' => 'Access Denied'], 403);
        }

        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $key = $data['key'] ?? '';

        if (empty($key)) {
            return new JsonResponse(['success' => false, 'message' => '文件key不能为空'], 400);
        }

        try {
            $qiniuService = new QiniuUploadService();
            $signedUrl = $qiniuService->getPrivateUrl($key);

            return new JsonResponse([
                'success' => true,
                'url' => $signedUrl,
                'key' => $key
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => '获取签名URL失败：' . $e->getMessage()
            ], 500);
        }
    }
}