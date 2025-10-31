<?php

namespace App\Controller\Admin;

use App\Entity\Supplier;
use App\Entity\Withdrawal;
use App\Service\BalanceHistoryService;
use App\Service\FinancialCalculatorService;
use App\Service\PathConfigService;
use App\Service\QiniuUploadService;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin{adminLoginPath}/withdrawal', requirements: ['adminLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('ADMIN_ACTIVE')]
class WithdrawalController extends AbstractController
{
    private $entityManager;
    private $adminLoginPath;
    private $financialCalculator;
    private $balanceHistoryService;

    public function __construct(EntityManagerInterface $entityManager, FinancialCalculatorService $financialCalculator, BalanceHistoryService $balanceHistoryService)
    {
        $this->entityManager = $entityManager;
        $this->financialCalculator = $financialCalculator;
        $this->balanceHistoryService = $balanceHistoryService;
    }

    private function getAdminLoginPath(): string
    {
        return PathConfigService::getAdminLoginPath();
    }

    /**
     * 提现申请列表页面
     */
    #[Route('/list/page', name: 'admin_withdrawal_list_page', methods: ['GET'])]
    public function listPage(): Response
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        return $this->render('admin/withdrawal_list.html.twig', [
            'adminLoginPath' => $this->getAdminLoginPath()
        ]);
    }

    /**
     * 获取提现申请列表
     */
    #[Route('/list', name: 'admin_withdrawal_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse|Response
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        $status = $request->query->get('status');
        $supplierName = $request->query->get('supplierName');
        $reviewedAtStart = $request->query->get('reviewedAtStart');
        $reviewedAtEnd = $request->query->get('reviewedAtEnd');
        $export = $request->query->get('export');

        // 构建查询条件
        $criteria = [];
        if ($status) {
            $criteria['status'] = $status;
        }

        // 添加审核时间范围条件
        if ($reviewedAtStart && $reviewedAtEnd) {
            $criteria['reviewedAtStart'] = $reviewedAtStart;
            $criteria['reviewedAtEnd'] = $reviewedAtEnd;
        } elseif ($reviewedAtStart) {
            $criteria['reviewedAtStart'] = $reviewedAtStart;
        } elseif ($reviewedAtEnd) {
            $criteria['reviewedAtEnd'] = $reviewedAtEnd;
        }

        // 如果是导出请求，获取所有数据
        if ($export === 'excel') {
            return $this->exportToExcel($criteria);
        }

        // 如果有供应商名称筛选，先查找供应商ID
        if ($supplierName) {
            $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->createQueryBuilder('s')
                ->select('s.id')
                ->where('s.username LIKE :supplierName OR s.companyName LIKE :supplierName')
                ->setParameter('supplierName', "%{$supplierName}%")
                ->getQuery()
                ->getResult();
            
            $supplierIds = array_column($suppliers, 'id');
            if (!empty($supplierIds)) {
                // 使用所有匹配的供应商ID进行查询
                $criteria['supplierIds'] = $supplierIds;
            } else {
                // 如果没有找到匹配的供应商，返回空结果
                return $this->json([
                    'success' => true,
                    'data' => [],
                    'pagination' => [
                        'currentPage' => $page,
                        'totalItems' => 0,
                        'totalPages' => 0,
                        'itemsPerPage' => $limit,
                    ],
                ]);
            }
        }

        // 使用Repository获取分页数据
        $repository = $this->entityManager->getRepository(Withdrawal::class);
        $result = $repository->findWithPagination($page, $limit, $criteria);

        // 获取供应商信息
        $supplierIds = [];
        foreach ($result['items'] as $withdrawal) {
            $supplierIds[] = $withdrawal->getSupplierId();
        }

        $suppliers = [];
        if (!empty($supplierIds)) {
            $supplierEntities = $this->entityManager->getRepository(Supplier::class)
                ->findBy(['id' => $supplierIds]);
            
            foreach ($supplierEntities as $supplier) {
                $suppliers[$supplier->getId()] = $supplier;
            }
        }

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
            $supplier = $suppliers[$withdrawal->getSupplierId()] ?? null;
            
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
                'username' => $supplier ? $supplier->getUsername() : '未知供应商',
                'companyName' => $supplier ? ($supplier->getCompanyName() ?: '无') : '无',
                'amount' => $withdrawal->getAmount(),
                'paymentInfo' => $withdrawal->getPaymentInfo(), // 从 payment_info 字段读取收款信息
                'paymentScreenshot' => $paymentScreenshotKey,  // 原始key
                'paymentScreenshotUrl' => $paymentScreenshotUrl,  // 带签名的URL，用于显示
                'status' => $withdrawal->getStatus(),
                'remark' => $withdrawal->getRemark(),
                'reviewedBy' => $withdrawal->getReviewedBy(),
                'reviewedAt' => $withdrawal->getReviewedAt() ? $withdrawal->getReviewedAt()->format('Y-m-d H:i:s') : null,
                'createdAt' => $withdrawal->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $withdrawal->getUpdatedAt() ? $withdrawal->getUpdatedAt()->format('Y-m-d H:i:s') : null,
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
     * 导出提现申请到Excel
     */
    private function exportToExcel(array $criteria): Response
    {
        // 获取所有符合条件的数据
        $repository = $this->entityManager->getRepository(Withdrawal::class);
        
        // 构建查询
        $qb = $repository->createQueryBuilder('w');

        // 添加筛选条件
        if (isset($criteria['status']) && $criteria['status'] !== '') {
            $qb->andWhere('w.status = :status')
                ->setParameter('status', $criteria['status']);
        }

        if (isset($criteria['supplierIds']) && !empty($criteria['supplierIds'])) {
            $qb->andWhere('w.supplierId IN (:supplierIds)')
                ->setParameter('supplierIds', $criteria['supplierIds']);
        }

        // 添加审核时间范围条件
        if (isset($criteria['reviewedAtStart']) && $criteria['reviewedAtStart'] !== '' && 
            isset($criteria['reviewedAtEnd']) && $criteria['reviewedAtEnd'] !== '') {
            // 日期范围查询
            $qb->andWhere('w.reviewedAt >= :reviewedAtStart AND w.reviewedAt <= :reviewedAtEnd')
                ->setParameter('reviewedAtStart', $criteria['reviewedAtStart'])
                ->setParameter('reviewedAtEnd', $criteria['reviewedAtEnd']);
        } elseif (isset($criteria['reviewedAtStart']) && $criteria['reviewedAtStart'] !== '') {
            // 只有开始时间
            $qb->andWhere('w.reviewedAt >= :reviewedAtStart')
                ->setParameter('reviewedAtStart', $criteria['reviewedAtStart']);
        } elseif (isset($criteria['reviewedAtEnd']) && $criteria['reviewedAtEnd'] !== '') {
            // 只有结束时间
            $qb->andWhere('w.reviewedAt <= :reviewedAtEnd')
                ->setParameter('reviewedAtEnd', $criteria['reviewedAtEnd']);
        }

        // 按审核时间排序
        $qb->orderBy('w.reviewedAt', 'DESC');

        // 获取所有数据
        $withdrawals = $qb->getQuery()->getResult();

        // 获取供应商信息
        $supplierIds = [];
        foreach ($withdrawals as $withdrawal) {
            $supplierIds[] = $withdrawal->getSupplierId();
        }

        $suppliers = [];
        if (!empty($supplierIds)) {
            $supplierEntities = $this->entityManager->getRepository(Supplier::class)
                ->findBy(['id' => $supplierIds]);
            
            foreach ($supplierEntities as $supplier) {
                $suppliers[$supplier->getId()] = $supplier;
            }
        }

        // 创建Excel文件
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 设置标题行
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', '供应商');
        $sheet->setCellValue('C1', '提现金额');
        $sheet->setCellValue('D1', '状态');
        $sheet->setCellValue('E1', '审核人');
        $sheet->setCellValue('F1', '审核时间');
        $sheet->setCellValue('G1', '申请时间');
        
        // 设置标题样式
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E0E0E0',
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
        
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        
        // 填充数据
        $row = 2;
        foreach ($withdrawals as $withdrawal) {
            $supplier = $suppliers[$withdrawal->getSupplierId()] ?? null;
            $supplierName = $supplier ? ($supplier->getUsername() . '/' . ($supplier->getCompanyName() ?: '无')) : '未知供应商';
            $statusText = $withdrawal->getStatusText();
            $reviewedAt = $withdrawal->getReviewedAt() ? $withdrawal->getReviewedAt()->format('Y-m-d H:i:s') : '';
            $createdAt = $withdrawal->getCreatedAt()->format('Y-m-d H:i:s');
            
            $sheet->setCellValue('A' . $row, $withdrawal->getId());
            $sheet->setCellValue('B' . $row, $supplierName);
            $sheet->setCellValue('C' . $row, $withdrawal->getAmount());
            $sheet->setCellValue('D' . $row, $statusText);
            $sheet->setCellValue('E' . $row, $withdrawal->getReviewedBy() ?? '');
            $sheet->setCellValue('F' . $row, $reviewedAt);
            $sheet->setCellValue('G' . $row, $createdAt);
            
            $row++;
        }
        
        // 自动调整列宽
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // 创建响应
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'withdrawal_export_');
        $writer->save($tempFile);
        
        $response = new BinaryFileResponse($tempFile);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="withdrawal_list_' . date('Y-m-d_H-i-s') . '.xlsx"');
        $response->deleteFileAfterSend(true);
        
        return $response;
    }

    /**
     * 审核提现申请
     */
    #[Route('/{id}/review', name: 'admin_withdrawal_review', methods: ['POST'])]
    public function review(int $id, Request $request): JsonResponse
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        // 获取提现申请
        $withdrawal = $this->entityManager->getRepository(Withdrawal::class)->find($id);
        if (!$withdrawal) {
            return $this->json([
                'success' => false,
                'message' => '提现申请不存在',
            ], 404);
        }

        // 解析请求数据
        $data = json_decode($request->getContent(), true);
        $action = $data['action'] ?? '';
        $remark = $data['remark'] ?? '';

        // 验证操作类型
        if (!in_array($action, ['approved', 'rejected'])) {
            return $this->json([
                'success' => false,
                'message' => '无效的操作类型',
            ], 400);
        }

        // 开始事务处理
        $this->entityManager->getConnection()->beginTransaction();
        try {
            // 更新提现申请状态
            $withdrawal->setStatus($action);
            $withdrawal->setRemark($remark);
            $withdrawal->setReviewedBy($this->getUser()->getUserIdentifier());
            $withdrawal->setReviewedAt(new \DateTime());
            $withdrawal->setUpdatedAt(new \DateTime());

            // 获取供应商信息
            $supplier = $this->entityManager->getRepository(Supplier::class)->find($withdrawal->getSupplierId());
            if (!$supplier) {
                throw new \Exception('供应商不存在');
            }

            // 如果审核通过，需要解冻冻结的金额
            if ($action === 'approved') {
                // 获取供应商当前余额和冻结余额
                $currentBalance = $supplier->getBalance() ?? '0.00';
                $frozenBalance = $supplier->getBalanceFrozen() ?? '0.00';
                
                // 计算新的冻结余额（解冻提现金额）
                $newFrozenBalance = $this->financialCalculator->subtract($frozenBalance, $withdrawal->getAmount());
                // 可用余额保持不变（因为在申请提现时已经扣减过了）
                $newBalance = $currentBalance;
                
                // 更新供应商余额
                $supplier->setBalance($newBalance);
                $supplier->setBalanceFrozen($newFrozenBalance);
                $supplier->setUpdatedAt(new \DateTime());
                
                // 保存供应商信息
                $this->entityManager->persist($supplier);

                // 创建余额变化记录（解冻）
                $balanceHistory = $this->balanceHistoryService->createBalanceHistory(
                    'supplier',
                    $supplier->getId(),
                    $currentBalance, // 变化前余额
                    $newBalance, // 变化后余额
                    '0.00', // 变化金额（无变化）
                    $frozenBalance, // 变化前冻结余额
                    $newFrozenBalance, // 变化后冻结余额
                    $this->financialCalculator->subtract('0', $withdrawal->getAmount()), // 冻结余额变化金额（负数表示解冻）
                    'withdraw', // 业务类型
                    '提现审核通过：¥' . number_format((float)$withdrawal->getAmount(), 2) . ' 已解冻', // 变化描述
                    (string)$withdrawal->getId() // 关联业务ID（提现ID）
                );
                
                // 更新提现申请关联的余额历史记录ID
                $withdrawal->setBalanceHistoryId($balanceHistory->getId());
            } 
            // 如果审核拒绝，需要解冻冻结的金额，并将金额加回到可用余额
            elseif ($action === 'rejected') {
                // 获取供应商当前余额和冻结余额
                $currentBalance = $supplier->getBalance() ?? '0.00';
                $frozenBalance = $supplier->getBalanceFrozen() ?? '0.00';
                
                // 计算新的冻结余额（解冻提现金额）
                $newFrozenBalance = $this->financialCalculator->subtract($frozenBalance, $withdrawal->getAmount());
                // 将提现金额加回到可用余额
                $newBalance = $this->financialCalculator->add($currentBalance, $withdrawal->getAmount());
                
                // 更新供应商余额
                $supplier->setBalance($newBalance);
                $supplier->setBalanceFrozen($newFrozenBalance);
                $supplier->setUpdatedAt(new \DateTime());
                
                // 保存供应商信息
                $this->entityManager->persist($supplier);

                // 创建余额变化记录（解冻并恢复可用余额）
                $balanceHistory = $this->balanceHistoryService->createBalanceHistory(
                    'supplier',
                    $supplier->getId(),
                    $currentBalance, // 变化前余额
                    $newBalance, // 变化后余额
                    $withdrawal->getAmount(), // 变化金额（正数表示收入）
                    $frozenBalance, // 变化前冻结余额
                    $newFrozenBalance, // 变化后冻结余额
                    $this->financialCalculator->subtract('0', $withdrawal->getAmount()), // 冻结余额变化金额（负数表示解冻）
                    'withdraw', // 业务类型
                    '提现审核拒绝：¥' . number_format((float)$withdrawal->getAmount(), 2) . ' 已解冻并返回', // 变化描述
                    (string)$withdrawal->getId() // 关联业务ID（提现ID）
                );
                
                // 更新提现申请关联的余额历史记录ID
                $withdrawal->setBalanceHistoryId($balanceHistory->getId());
            }

            // 保存提现申请更改
            $this->entityManager->persist($withdrawal);

            // 提交事务
            $this->entityManager->getConnection()->commit();
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => '审核成功',
            ]);
        } catch (\Exception $e) {
            // 回滚事务
            $this->entityManager->getConnection()->rollBack();
            return $this->json([
                'success' => false,
                'message' => '审核失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 获取供应商列表（用于选择供应商）
     */
    #[Route('/suppliers', name: 'admin_withdrawal_suppliers', methods: ['GET'])]
    public function getSuppliers(): JsonResponse
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        // 获取所有供应商
        $suppliers = $this->entityManager->getRepository(Supplier::class)->findAll();

        // 格式化数据
        $data = [];
        foreach ($suppliers as $supplier) {
            $data[] = [
                'id' => $supplier->getId(),
                'username' => $supplier->getUsername(),
                'companyName' => $supplier->getCompanyName(),
                'balance' => $supplier->getBalance(),
            ];
        }

        return $this->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * 上传打款截图
     */
    #[Route('/{id}/upload-payment-screenshot', name: 'admin_withdrawal_upload_payment_screenshot', methods: ['POST'])]
    public function uploadPaymentScreenshot(int $id, Request $request): JsonResponse
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        // 获取提现申请
        $withdrawal = $this->entityManager->getRepository(Withdrawal::class)->find($id);
        if (!$withdrawal) {
            return $this->json([
                'success' => false,
                'message' => '提现申请不存在',
            ], 404);
        }

        // 只有已通过状态的提现申请才能上传打款截图
        if (!$withdrawal->isApproved()) {
            return $this->json([
                'success' => false,
                'message' => '只有已通过状态的提现申请才能上传打款截图',
            ], 400);
        }

        try {
            // 获取上传的文件
            $uploadedFile = $request->files->get('image');

            if (!$uploadedFile || !$uploadedFile->isValid()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => '文件上传失败'
                ], 400);
            }

            // 验证文件类型
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
            $fileName = 'withdrawal_payment_' . $id . '_' . time() . '_' . uniqid() . '.' . $extension;

            // 上传文件到七牛云
            $uploadResult = $qiniuService->uploadFile($uploadedFile->getPathname(), $fileName);

            if (!$uploadResult['success']) {
                return new JsonResponse([
                    'success' => false,
                    'error' => $uploadResult['error']
                ], 500);
            }

            // 更新提现申请的打款截图字段
            $withdrawal->setPaymentScreenshot($uploadResult['key']);
            $withdrawal->setUpdatedAt(new \DateTime());

            // 保存更改
            $this->entityManager->persist($withdrawal);
            $this->entityManager->flush();

            // 返回上传结果
            return new JsonResponse([
                'success' => true,
                'url' => $uploadResult['key'],        // 存储到数据库的key
                'key' => $uploadResult['key'],
                'previewUrl' => $uploadResult['url'], // 用于立即预览的带签名URL
                'message' => '打款截图上传成功'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => '文件上传失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取打款截图签名URL
     */
    #[Route('/payment-screenshot/signed-url', name: 'admin_withdrawal_payment_screenshot_signed_url', methods: ['POST'])]
    public function getPaymentScreenshotSignedUrl(Request $request): JsonResponse
    {
        // 检查权限
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_ADMIN_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        try {
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