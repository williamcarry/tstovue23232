<?php

namespace App\Controller\Supplier;

use App\Entity\BalanceHistory;
use App\Entity\Supplier;
use App\Entity\SupplierSubAccount;
use App\Entity\Withdrawal;
use App\Service\BalanceHistoryService;
use App\Service\CommissionService;
use App\Service\FinancialCalculatorService;
use App\Service\PathConfigService;
use App\Service\SiteConfigService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/supplier{supplierLoginPath}/balance-vip', requirements: ['supplierLoginPath' => '[a-zA-Z0-9]+'])]
#[IsGranted('SUPPLIER_ACTIVE')]
class BalanceVipController extends AbstractController
{
    private $entityManager;
    private $commissionService;
    private $siteConfigService;
    private $financialCalculator;
    private $balanceHistoryService;

    public function __construct(EntityManagerInterface $entityManager, CommissionService $commissionService, SiteConfigService $siteConfigService, FinancialCalculatorService $financialCalculator, BalanceHistoryService $balanceHistoryService)
    {
        $this->entityManager = $entityManager;
        $this->commissionService = $commissionService;
        $this->siteConfigService = $siteConfigService;
        $this->financialCalculator = $financialCalculator;
        $this->balanceHistoryService = $balanceHistoryService;
    }

    /**
     * 余额&VIP页面
     */
    #[Route('/index', name: 'supplier_balance_vip_index', methods: ['GET'])]
    public function index(string $supplierLoginPath): Response
    {
        // 验证路径是否正确
        if ($supplierLoginPath !== PathConfigService::getSupplierLoginPath()) {
            throw $this->createNotFoundException('Invalid supplier path');
        }

        // 检查权限
        if (!$this->isGranted('ROLE_SUPPLIER_SUPER') && !$this->isGranted('ROLE_SUPPLIER_BALANCE')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        /** @var Supplier|SupplierSubAccount $user */
        $user = $this->getUser();
        
        // 如果是子账号，获取主账号信息
        $supplier = $this->getMainSupplier($user);

        return $this->render('supplier/_balance_vip_inner.html.twig', [
            'supplierLoginPath' => $supplierLoginPath,
            'supplier' => $supplier,
        ]);
    }

    /**
     * 获取供应商余额和VIP信息
     */
    #[Route('/data', name: 'supplier_balance_vip_data', methods: ['GET'])]
    public function getBalanceVipData(string $supplierLoginPath): JsonResponse
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

        // 获取原始佣金比例
        $originalCommissionRate = $supplier->getCommissionRate();
        
        // 计算原始佣金比例的百分比形式（即使会员过期也显示）
        $originalCommissionRatePercentage = null;
        if ($originalCommissionRate !== null) {
            $originalCommissionRatePercentage = (float) bcmul($originalCommissionRate, '100', 2);
        }
        
        // 获取会员价格配置
        $membershipPrices = $this->siteConfigService->getAllMembershipPrices();
        
        // 获取供应商的默认收款信息
        $defaultPaymentInfo = $this->getDefaultPaymentInfo($supplier);
        
        // 格式化数据
        $data = [
            'id' => $supplier->getId(),
            'balance' => $supplier->getBalance(),
            'balanceFrozen' => $supplier->getBalanceFrozen(),
            'membershipType' => $supplier->getMembershipType(),
            'membershipExpiredAt' => $supplier->getMembershipExpiredAt() ? $supplier->getMembershipExpiredAt()->format('Y-m-d H:i:s') : null,
            'commissionRate' => $supplier->getCommissionRate(),
            'commissionRatePercentage' => $this->commissionService->getEffectiveCommissionRatePercentage($supplier),
            'isMemberActive' => $supplier->isMemberActive(),
            // 即使会员过期，也提供原始佣金比例用于显示
            'originalCommissionRate' => $originalCommissionRate,
            'originalCommissionRatePercentage' => $originalCommissionRatePercentage,
            'membershipPrices' => $membershipPrices,
            'defaultPaymentInfo' => $defaultPaymentInfo,
        ];

        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * 申请提现
     */
    #[Route('/withdraw', name: 'supplier_balance_vip_withdraw', methods: ['POST'])]
    public function withdraw(Request $request, string $supplierLoginPath): JsonResponse
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

        // 获取提现金额和收款信息
        $data = json_decode($request->getContent(), true);
        $amount = $data['amount'] ?? 0;
        $paymentInfo = $data['paymentInfo'] ?? '';

        // 验证收款信息
        if (empty($paymentInfo) || trim($paymentInfo) === '') {
            return new JsonResponse([
                'success' => false,
                'message' => '请填写收款信息'
            ]);
        }

        // 验证提现金额
        if ($amount <= 0) {
            return new JsonResponse([
                'success' => false,
                'message' => '提现金额必须大于0'
            ]);
        }

        // 验证提现金额格式（最多两位小数）
        if (!preg_match('/^\d+(\.\d{1,2})?$/', (string)$amount)) {
            return new JsonResponse([
                'success' => false,
                'message' => '提现金额最多保留两位小数'
            ]);
        }

        // 格式化金额为两位小数
        $amount = number_format((float)$amount, 2, '.', '');

        // 检查余额是否足够
        $balance = $supplier->getBalance() ?? '0.00';
        if (!$this->financialCalculator->isSufficient($balance, (string)$amount)) {
            return new JsonResponse([
                'success' => false,
                'message' => '余额不足，无法提现'
            ]);
        }

        // 开始事务处理
        $this->entityManager->getConnection()->beginTransaction();
        try {
            // 创建提现申请记录
            $withdrawal = new Withdrawal();
            $withdrawal->setSupplierId($supplier->getId());
            $withdrawal->setAmount((string)$amount);
            $withdrawal->setPaymentInfo(trim($paymentInfo)); // 保存收款信息
            $withdrawal->setCreatedAt(new \DateTime());
            $withdrawal->setUpdatedAt(new \DateTime());
            
            $this->entityManager->persist($withdrawal);
            $this->entityManager->flush(); // 需要先flush获取ID

            // 更新供应商余额（冻结提现金额）
            $frozenBalance = $supplier->getBalanceFrozen() ?? '0.00';
            $newFrozenBalance = $this->financialCalculator->add($frozenBalance, (string)$amount);
            $newBalance = $this->financialCalculator->subtract($balance, (string)$amount);
            
            $supplier->setBalance($newBalance);
            $supplier->setBalanceFrozen($newFrozenBalance);
            $supplier->setUpdatedAt(new \DateTime());
            
            $this->entityManager->persist($supplier);

            // 创建余额变化记录
            $this->balanceHistoryService->createBalanceHistory(
                'supplier',
                $supplier->getId(),
                $balance, // 变化前余额
                $newBalance, // 变化后余额
                $this->financialCalculator->subtract('0', (string)$amount), // 变化金额（负数表示支出）
                $frozenBalance, // 变化前冻结余额
                $newFrozenBalance, // 变化后冻结余额
                (string)$amount, // 冻结余额变化金额（正数表示冻结增加）
                'withdraw', // 业务类型
                '提现申请：¥' . number_format((float)$amount, 2), // 变化描述
                (string)$withdrawal->getId() // 关联业务ID（提现ID）
            );

            // 提交事务
            $this->entityManager->getConnection()->commit();
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => '提现申请已提交，请等待审核'
            ]);
        } catch (\Exception $e) {
            // 回滚事务
            $this->entityManager->getConnection()->rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => '提现申请失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 购买会员 - 确认信息
     */
    #[Route('/purchase-membership-confirm', name: 'supplier_balance_vip_purchase_membership_confirm', methods: ['POST'])]
    public function confirmPurchaseMembership(Request $request, string $supplierLoginPath): JsonResponse
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

        // 获取会员类型
        $data = json_decode($request->getContent(), true);
        $membershipType = $data['membershipType'] ?? '';

        // 会员类型映射（按等级排序）
        $membershipTypes = [
            'monthly' => ['name' => '月会员', 'months' => 1, 'level' => 1],
            'quarterly' => ['name' => '季度会员', 'months' => 3, 'level' => 2],
            'half_yearly' => ['name' => '半年会员', 'months' => 6, 'level' => 3],
            'yearly' => ['name' => '年会员', 'months' => 12, 'level' => 4],
        ];

        // 验证会员类型
        if (!isset($membershipTypes[$membershipType])) {
            return new JsonResponse([
                'success' => false,
                'message' => '无效的会员类型'
            ]);
        }

        // 获取会员价格
        $membershipPrices = $this->siteConfigService->getAllMembershipPrices();
        $price = $membershipPrices[$membershipType] ?? '0.00';
        
        // 检查余额是否足够支付会员费用
        $balance = $supplier->getBalance() ?? '0.00';
        if (!$this->financialCalculator->isSufficient($balance, $price)) {
            return new JsonResponse([
                'success' => false,
                'message' => '余额不足，无法购买会员'
            ]);
        }

        // 返回确认信息
        return new JsonResponse([
            'success' => true,
            'data' => [
                'membershipType' => $membershipType,
                'membershipName' => $membershipTypes[$membershipType]['name'],
                'price' => $price,
                'currentMembershipType' => $supplier->getMembershipType(),
                'currentMembershipExpiredAt' => $supplier->getMembershipExpiredAt() ? $supplier->getMembershipExpiredAt()->format('Y-m-d H:i:s') : null,
                'isMemberActive' => $supplier->isMemberActive(),
            ]
        ]);
    }

    /**
     * 添加月份到日期（更精确的方法）
     */
    private function addMonthsToDate(\DateTime $date, int $months): \DateTime
    {
        $newDate = clone $date;
        
        // 获取当前的年、月、日、时、分、秒
        $currentYear = (int)$newDate->format('Y');
        $currentMonth = (int)$newDate->format('m');
        $currentDay = (int)$newDate->format('d');
        $currentHour = (int)$newDate->format('H');
        $currentMinute = (int)$newDate->format('i');
        $currentSecond = (int)$newDate->format('s');
        
        // 计算新的年月
        $totalMonths = $currentMonth + $months;
        $newYear = $currentYear + floor(($totalMonths - 1) / 12);
        $newMonth = (($totalMonths - 1) % 12) + 1;
        
        // 处理日期溢出情况（如1月31日加1个月应该是2月28日或29日）
        $daysInNewMonth = cal_days_in_month(CAL_GREGORIAN, $newMonth, $newYear);
        $newDay = min($currentDay, $daysInNewMonth);
        
        // 设置新的日期
        $newDate->setDate($newYear, $newMonth, $newDay);
        $newDate->setTime($currentHour, $currentMinute, $currentSecond);
        
        return $newDate;
    }

    /**
     * 购买会员
     */
    #[Route('/purchase-membership', name: 'supplier_balance_vip_purchase_membership', methods: ['POST'])]
    public function purchaseMembership(Request $request, string $supplierLoginPath): JsonResponse
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

        // 获取会员类型
        $data = json_decode($request->getContent(), true);
        $membershipType = $data['membershipType'] ?? '';

        // 会员类型映射
        $membershipTypes = [
            'monthly' => ['name' => '月会员', 'months' => 1, 'level' => 1],
            'quarterly' => ['name' => '季度会员', 'months' => 3, 'level' => 2],
            'half_yearly' => ['name' => '半年会员', 'months' => 6, 'level' => 3],
            'yearly' => ['name' => '年会员', 'months' => 12, 'level' => 4],
        ];

        // 验证会员类型
        if (!isset($membershipTypes[$membershipType])) {
            return new JsonResponse([
                'success' => false,
                'message' => '无效的会员类型'
            ]);
        }

        // 获取会员价格
        $membershipPrices = $this->siteConfigService->getAllMembershipPrices();
        $price = $membershipPrices[$membershipType] ?? '0.00';
        
        // 检查余额是否足够支付会员费用
        $balance = $supplier->getBalance() ?? '0.00';
        if (!$this->financialCalculator->isSufficient($balance, $price)) {
            return new JsonResponse([
                'success' => false,
                'message' => '余额不足，无法购买会员'
            ]);
        }

        // 开始事务处理
        $this->entityManager->getConnection()->beginTransaction();
        try {
            // 扣除余额
            $newBalance = $this->financialCalculator->subtract($balance, $price);
            $supplier->setBalance($newBalance);

            // 计算新的会员到期时间
            $now = new \DateTime();
            $months = $membershipTypes[$membershipType]['months'];
            
            // 检查当前会员状态
            $currentMembershipType = $supplier->getMembershipType();
            $isCurrentMemberActive = $supplier->isMemberActive();
            
            // 如果当前会员有效，则在现有到期时间基础上延长
            if ($isCurrentMemberActive) {
                // 将DateTimeInterface转换为DateTime对象
                $expiredAt = new \DateTime($supplier->getMembershipExpiredAt()->format('Y-m-d H:i:s'));
                $expiredAt = $this->addMonthsToDate($expiredAt, $months);
                
                // 会员有效期内，只能升级不能降级
                if ($currentMembershipType && $currentMembershipType !== 'none') {
                    $currentLevel = $membershipTypes[$currentMembershipType]['level'] ?? 0;
                    $newLevel = $membershipTypes[$membershipType]['level'] ?? 0;
                    
                    // 如果新等级高于当前等级，则升级会员类型
                    // 否则保持当前会员类型不变
                    if ($newLevel > $currentLevel) {
                        $supplier->setMembershipType($membershipType);
                    }
                    // 如果新等级低于或等于当前等级，不改变会员类型，只延长有效期
                }
            } else {
                // 如果当前没有会员或会员已过期，则从现在开始计算，并设置新的会员类型
                $expiredAt = $this->addMonthsToDate($now, $months);
                $supplier->setMembershipType($membershipType);
            }

            $supplier->setMembershipExpiredAt($expiredAt);
            $supplier->setUpdatedAt(new \DateTime());

            // 保存供应商信息
            $this->entityManager->persist($supplier);

            // 创建余额变化记录
            $balanceHistory = new BalanceHistory();
            $balanceHistory->setUserType('supplier');
            $balanceHistory->setUserId($supplier->getId());
            $balanceHistory->setBalanceBefore($balance);
            $balanceHistory->setBalanceAfter($newBalance);
            $balanceHistory->setAmount($this->financialCalculator->subtract('0', $price)); // 负数表示支出
            $balanceHistory->setFrozenBalanceBefore($supplier->getBalanceFrozen() ?? '0.00');
            $balanceHistory->setFrozenBalanceAfter($supplier->getBalanceFrozen() ?? '0.00');
            $balanceHistory->setFrozenAmount('0.00');
            $balanceHistory->setType('vip_purchase');
            $balanceHistory->setDescription('购买' . $membershipTypes[$membershipType]['name']);
            $balanceHistory->setCreatedAt(new \DateTime());

            $this->entityManager->persist($balanceHistory);

            // 提交事务
            $this->entityManager->getConnection()->commit();
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => '成功购买' . $membershipTypes[$membershipType]['name'],
                'data' => [
                    'membershipType' => $membershipType,
                    'membershipExpiredAt' => $expiredAt->format('Y-m-d H:i:s'),
                    'isMemberActive' => $supplier->isMemberActive(),
                    'balance' => $newBalance,
                ]
            ]);
        } catch (\Exception $e) {
            // 回滚事务
            $this->entityManager->getConnection()->rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => '购买失败：' . $e->getMessage()
            ], 500);
        }
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

    /**
     * 获取供应商的默认收款信息
     */
    private function getDefaultPaymentInfo(Supplier $supplier): string
    {
        $paymentInfo = [];
        
        // 根据供应商类型获取不同的银行卡信息
        if ($supplier->getSupplierType() === 'company') {
            // 公司类型：使用公司银行账户
            if ($supplier->getBankAccountName()) {
                $paymentInfo[] = '开户名称：' . $supplier->getBankAccountName();
            }
            if ($supplier->getBankAccountNumber()) {
                $paymentInfo[] = '银行账号：' . $supplier->getBankAccountNumber();
            }
            if ($supplier->getBankName()) {
                $paymentInfo[] = '开户银行：' . $supplier->getBankName();
            }
            if ($supplier->getBankBranch()) {
                $paymentInfo[] = '开户支行：' . $supplier->getBankBranch();
            }
        } else if ($supplier->getSupplierType() === 'individual') {
            // 个人类型：使用个人银行账户
            if ($supplier->getIndividualName()) {
                $paymentInfo[] = '收款人名：' . $supplier->getIndividualName();
            }
            if ($supplier->getIndividualBankAccount()) {
                $paymentInfo[] = '银行账号：' . $supplier->getIndividualBankAccount();
            }
            if ($supplier->getIndividualBankName()) {
                $paymentInfo[] = '开户银行：' . $supplier->getIndividualBankName();
            }
        }
        
        return implode("\n", $paymentInfo);
    }
}