<?php

namespace App\Command;

use App\Entity\Supplier;
use App\Service\FinancialCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestMembershipPurchaseCommand extends Command
{
    protected static $defaultName = 'app:test-membership-purchase';
    
    private EntityManagerInterface $entityManager;
    private FinancialCalculatorService $financialCalculator;

    public function __construct(EntityManagerInterface $entityManager, FinancialCalculatorService $financialCalculator)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->financialCalculator = $financialCalculator;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:test-membership-purchase')
            ->setDescription('测试会员购买功能');
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 创建测试供应商
        $supplier = new Supplier();
        $supplier->setUsername('test_member_' . time());
        $supplier->setEmail('test_member_' . time() . '@example.com');
        $supplier->setPassword('test_password');
        $supplier->setContactPerson('Test Member');
        $supplier->setContactPhone('12345678901');
        $supplier->setBalance('1000.00'); // 设置初始余额1000元

        $this->entityManager->persist($supplier);
        $this->entityManager->flush();

        $io->success('创建测试供应商，ID: ' . $supplier->getId());

        // 测试购买月会员
        $io->section('测试购买月会员');
        
        // 检查余额
        $balance = $supplier->getBalance();
        $io->text('当前余额: ' . $balance);

        // 会员价格（假设月会员价格为29.90元）
        $membershipPrice = '29.90';
        
        // 检查余额是否足够
        if ($this->financialCalculator->isSufficient($balance, $membershipPrice)) {
            $io->text('余额足够购买月会员');
            
            // 扣除费用
            $newBalance = $this->financialCalculator->subtract($balance, $membershipPrice);
            $supplier->setBalance($newBalance);
            
            // 设置会员信息
            $now = new \DateTime();
            $expiredAt = $this->addMonthsToDate($now, 1);
            
            $supplier->setMembershipType('monthly');
            $supplier->setMembershipExpiredAt($expiredAt);
            
            $this->entityManager->persist($supplier);
            $this->entityManager->flush();
            
            $io->success('成功购买月会员');
            $io->text('新余额: ' . $newBalance);
            $io->text('会员到期时间: ' . $expiredAt->format('Y-m-d H:i:s'));
        } else {
            $io->error('余额不足，无法购买月会员');
            return Command::FAILURE;
        }

        // 测试续费月会员
        $io->section('测试续费月会员');
        
        // 检查余额
        $balance = $supplier->getBalance();
        $io->text('当前余额: ' . $balance);
        
        // 检查余额是否足够
        if ($this->financialCalculator->isSufficient($balance, $membershipPrice)) {
            $io->text('余额足够续费月会员');
            
            // 扣除费用
            $newBalance = $this->financialCalculator->subtract($balance, $membershipPrice);
            $supplier->setBalance($newBalance);
            
            // 延长会员时间
            $expiredAt = new \DateTime($supplier->getMembershipExpiredAt()->format('Y-m-d H:i:s'));
            $expiredAt = $this->addMonthsToDate($expiredAt, 1);
            $supplier->setMembershipExpiredAt($expiredAt);
            
            $this->entityManager->persist($supplier);
            $this->entityManager->flush();
            
            $io->success('成功续费月会员');
            $io->text('新余额: ' . $newBalance);
            $io->text('会员到期时间: ' . $expiredAt->format('Y-m-d H:i:s'));
        } else {
            $io->error('余额不足，无法续费月会员');
            return Command::FAILURE;
        }

        // 测试购买季度会员
        $io->section('测试购买季度会员');
        
        // 检查余额
        $balance = $supplier->getBalance();
        $io->text('当前余额: ' . $balance);
        
        // 季度会员价格（假设季度会员价格为79.90元）
        $quarterlyMembershipPrice = '79.90';
        
        // 检查余额是否足够
        if ($this->financialCalculator->isSufficient($balance, $quarterlyMembershipPrice)) {
            $io->text('余额足够购买季度会员');
            
            // 扣除费用
            $newBalance = $this->financialCalculator->subtract($balance, $quarterlyMembershipPrice);
            $supplier->setBalance($newBalance);
            
            // 设置新的会员信息
            $now = new \DateTime();
            $expiredAt = $this->addMonthsToDate($now, 3);
            
            $supplier->setMembershipType('quarterly');
            $supplier->setMembershipExpiredAt($expiredAt);
            
            $this->entityManager->persist($supplier);
            $this->entityManager->flush();
            
            $io->success('成功购买季度会员');
            $io->text('新余额: ' . $newBalance);
            $io->text('会员到期时间: ' . $expiredAt->format('Y-m-d H:i:s'));
        } else {
            $io->error('余额不足，无法购买季度会员');
            return Command::FAILURE;
        }

        $io->success('所有测试完成！');
        return Command::SUCCESS;
    }
}