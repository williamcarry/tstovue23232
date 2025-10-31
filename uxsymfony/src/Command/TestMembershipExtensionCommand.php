<?php

namespace App\Command;

use App\Service\FinancialCalculatorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestMembershipExtensionCommand extends Command
{
    protected static $defaultName = 'app:test-membership-extension';
    
    private FinancialCalculatorService $financialCalculator;

    public function __construct(FinancialCalculatorService $financialCalculator)
    {
        parent::__construct();
        $this->financialCalculator = $financialCalculator;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:test-membership-extension')
            ->setDescription('测试会员延期逻辑');
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

        $io->title('会员延期逻辑测试');

        // 模拟场景：会员到期时间是2026-01-30 07:44:38，然后购买月会员
        $io->section('场景测试：会员到期时间是2026-01-30 07:44:38，然后购买月会员');
        $currentExpiredAt = new \DateTime('2026-01-30 07:44:38');
        $io->text('当前会员到期时间: ' . $currentExpiredAt->format('Y-m-d H:i:s'));
        
        // 购买月会员（延长1个月）
        $newExpiredAt = $this->addMonthsToDate($currentExpiredAt, 1);
        $io->text('购买月会员后的新到期时间: ' . $newExpiredAt->format('Y-m-d H:i:s'));
        $io->text('时间延长: ' . $currentExpiredAt->format('Y-m-d H:i:s') . ' → ' . $newExpiredAt->format('Y-m-d H:i:s'));

        // 模拟场景：会员到期时间是2026-01-30 07:44:38，然后购买季度会员
        $io->section('场景测试：会员到期时间是2026-01-30 07:44:38，然后购买季度会员');
        $currentExpiredAt2 = new \DateTime('2026-01-30 07:44:38');
        $io->text('当前会员到期时间: ' . $currentExpiredAt2->format('Y-m-d H:i:s'));
        
        // 购买季度会员（延长3个月）
        $newExpiredAt2 = $this->addMonthsToDate($currentExpiredAt2, 3);
        $io->text('购买季度会员后的新到期时间: ' . $newExpiredAt2->format('Y-m-d H:i:s'));
        $io->text('时间延长: ' . $currentExpiredAt2->format('Y-m-d H:i:s') . ' → ' . $newExpiredAt2->format('Y-m-d H:i:s'));

        // 模拟场景：会员到期时间是2026-10-30 07:44:38，然后购买年会员
        $io->section('场景测试：会员到期时间是2026-10-30 07:44:38，然后购买年会员');
        $currentExpiredAt3 = new \DateTime('2026-10-30 07:44:38');
        $io->text('当前会员到期时间: ' . $currentExpiredAt3->format('Y-m-d H:i:s'));
        
        // 购买年会员（延长12个月）
        $newExpiredAt3 = $this->addMonthsToDate($currentExpiredAt3, 12);
        $io->text('购买年会员后的新到期时间: ' . $newExpiredAt3->format('Y-m-d H:i:s'));
        $io->text('时间延长: ' . $currentExpiredAt3->format('Y-m-d H:i:s') . ' → ' . $newExpiredAt3->format('Y-m-d H:i:s'));

        // 测试金融计算
        $io->section('金融计算测试');
        $balance = '1000.00';
        $monthlyPrice = '29.90';
        $quarterlyPrice = '79.90';
        $yearlyPrice = '249.90';
        
        $io->text('余额: ' . $balance);
        $io->text('月会员价格: ' . $monthlyPrice);
        $io->text('季度会员价格: ' . $quarterlyPrice);
        $io->text('年会员价格: ' . $yearlyPrice);
        
        // 测试月会员购买
        if ($this->financialCalculator->isSufficient($balance, $monthlyPrice)) {
            $newBalance = $this->financialCalculator->subtract($balance, $monthlyPrice);
            $io->success('余额足够支付月会员');
            $io->text('新余额: ' . $newBalance);
        } else {
            $io->error('余额不足支付月会员');
        }

        $io->success('所有测试完成！');
        return Command::SUCCESS;
    }
}