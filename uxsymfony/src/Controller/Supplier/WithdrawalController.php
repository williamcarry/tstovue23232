<?php

namespace App\Controller\Supplier;

use App\Entity\Supplier;
use App\Entity\SupplierSubAccount;
use App\Entity\Withdrawal;
use App\Service\PathConfigService;
use App\Service\QiniuUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/supplier{supplierLoginPath}/withdrawal', requirements: ['supplierLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('SUPPLIER_ACTIVE')]
class WithdrawalController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * 提现申请列表页面
     */
    #[Route('/list/page', name: 'supplier_withdrawal_list_page', methods: ['GET'])]
    public function listPage(string $supplierLoginPath): Response
    {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            throw $this->createNotFoundException('Invalid supplier path');
        }

        // 检查权限
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        return $this->render('supplier/withdrawal_list.html.twig', [
            'supplierLoginPath' => $supplierLoginPath
        ]);
    }

    /**
     * 获取提现申请列表
     */
    #[Route('/list', name: 'supplier_withdrawal_list', methods: ['GET'])]
    public function list(Request $request, string $supplierLoginPath): JsonResponse
    {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid supplier path'], 404);
        }

        // 检查权限
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        /** @var Supplier|SupplierSubAccount $user */
        $user = $this->getUser();
        
        // 如果是子账号，获取主账号信息
        $supplier = $this->getMainSupplier($user);
        
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        $status = $request->query->get('status');

        // 构建查询条件
        $criteria = [
            'supplierId' => $supplier->getId()
        ];
        
        if ($status) {
            $criteria['status'] = $status;
        }

        // 使用Repository获取分页数据
        $repository = $this->entityManager->getRepository(Withdrawal::class);
        $result = $repository->findWithPagination($page, $limit, $criteria);

        // 创建七牛云服务实例
        try {
            $qiniuService = new QiniuUploadService();
        } catch (\Exception $e) {
            // 如果七牛云服务初始化失败，记录错误但继续返回数据（不包含图片URL）
            error_log('七牛云服务初始化失败: ' . $e->getMessage());
            $qiniuService = null;
        }

        // 格式化数据
        $data = [];
        foreach ($result['items'] as $withdrawal) {
            // 处理打款截图URL
            $paymentScreenshotKey = $withdrawal->getPaymentScreenshot();
            $paymentScreenshotUrl = '';
            
            // 只有在七牛云服务可用时才生成签名URL
            if ($qiniuService && $paymentScreenshotKey) {
                // 如果已经是完整URL（兼容旧数据），提取key后重新生成签名URL
                if (str_starts_with($paymentScreenshotKey, 'http')) {
                    $parts = parse_url($paymentScreenshotKey);
                    if (isset($parts['path'])) {
                        $paymentScreenshotKey = ltrim($parts['path'], '/');
                        // 移除查询参数中的签名
                        $paymentScreenshotKey = preg_replace('/\?.*$/', '', $paymentScreenshotKey);
                    }
                }
                
                try {
                    // 生成签名URL（有效期1小时）
                    $paymentScreenshotUrl = $qiniuService->getPrivateUrl($paymentScreenshotKey);
                } catch (\Exception $e) {
                    error_log('生成打款截图签名URL失败: ' . $e->getMessage());
                }
            }
            
            $data[] = [
                'id' => $withdrawal->getId(),
                'supplierId' => $withdrawal->getSupplierId(),
                'amount' => $withdrawal->getAmount(),
                'paymentInfo' => $withdrawal->getPaymentInfo(),
                'status' => $withdrawal->getStatus(),
                'statusText' => $withdrawal->getStatusText(),
                'remark' => $withdrawal->getRemark(),
                'reviewedBy' => $withdrawal->getReviewedBy(),
                'reviewedAt' => $withdrawal->getReviewedAt() ? $withdrawal->getReviewedAt()->format('Y-m-d H:i:s') : null,
                'createdAt' => $withdrawal->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $withdrawal->getUpdatedAt() ? $withdrawal->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                'paymentScreenshot' => $paymentScreenshotKey,  // 原始key
                'paymentScreenshotUrl' => $paymentScreenshotUrl,  // 带签名的URL，用于显示
            ];
        }

        return $this->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'currentPage' => $page,
                'totalItems' => $result['totalItems'],
                'totalPages' => $result['totalPages'],
                'itemsPerPage' => $limit,
            ],
        ]);
    }

    /**
     * 获取主账号信息
     * 如果是子账号登录，返回关联的主账号；如果是主账号登录，直接返回
     */
    private function getMainSupplier($user): Supplier
    {
        if ($user instanceof SupplierSubAccount) {
            // 如果是子账号，返回关联的主账号
            return $user->getSupplier();
        }
        
        // 如果是主账号，直接返回
        return $user;
    }
}