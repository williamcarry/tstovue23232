<?php

namespace App\Command;

use App\Service\FinancialCalculatorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestQuarterlyMembershipCommand extends Command
{
    protected static $defaultName = 'app:test-quarterly-membership';
    
    private FinancialCalculatorService $financialCalculator;

    public function __construct(FinancialCalculatorService $financialCalculator)
    {
        parent::__construct();
        $this->financialCalculator = $financialCalculator;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:test-quarterly-membership')
            ->setDescription('测试季度会员日期计算');
    }

    /**
     * 添加月份到日期（更精确的方法）
     */
    private function addMonthsToDate(\DateTime $date, int $months): \DateTime
    {
        $newDate = clone $date;
        
        // 获取当前的年、月、日
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

        $io->title('季度会员日期计算测试');

        // 测试用例1: 从2025-10-30开始，加3个月（季度会员）
        $io->section('测试用例1: 从2025-10-30开始，加3个月');
        $startDate1 = new \DateTime('2025-10-30 10:00:00');
        $io->text('开始日期: ' . $startDate1->format('Y-m-d H:i:s'));
        
        $endDate1 = $this->addMonthsToDate($startDate1, 3);
        $io->text('结束日期: ' . $endDate1->format('Y-m-d H:i:s'));
        $io->text('年份变化: ' . $startDate1->format('Y') . ' -> ' . $endDate1->format('Y'));
        $io->text('月份变化: ' . $startDate1->format('m') . ' -> ' . $endDate1->format('m'));

        // 测试用例2: 从2025-01-31开始，加3个月
        $io->section('测试用例2: 从2025-01-31开始，加3个月');
        $startDate2 = new \DateTime('2025-01-31 10:00:00');
        $io->text('开始日期: ' . $startDate2->format('Y-m-d H:i:s'));
        
        $endDate2 = $this->addMonthsToDate($startDate2, 3);
        $io->text('结束日期: ' . $endDate2->format('Y-m-d H:i:s'));
        $io->text('年份变化: ' . $startDate2->format('Y') . ' -> ' . $endDate2->format('Y'));
        $io->text('月份变化: ' . $startDate2->format('m') . ' -> ' . $endDate2->format('m'));

        // 测试用例3: 从2025-11-15开始，加3个月
        $io->section('测试用例3: 从2025-11-15开始，加3个月');
        $startDate3 = new \DateTime('2025-11-15 10:00:00');
        $io->text('开始日期: ' . $startDate3->format('Y-m-d H:i:s'));
        
        $endDate3 = $this->addMonthsToDate($startDate3, 3);
        $io->text('结束日期: ' . $endDate3->format('Y-m-d H:i:s'));
        $io->text('年份变化: ' . $startDate3->format('Y') . ' -> ' . $endDate3->format('Y'));
        $io->text('月份变化: ' . $startDate3->format('m') . ' -> ' . $endDate3->format('m'));

        // 测试用例4: 多次购买季度会员
        $io->section('测试用例4: 多次购买季度会员');
        $currentDate = new \DateTime('2025-10-30 10:00:00');
        $io->text('初始日期: ' . $currentDate->format('Y-m-d H:i:s'));
        
        for ($i = 1; $i <= 5; $i++) {
            $newDate = $this->addMonthsToDate($currentDate, 3);
            $io->text("第{$i}次购买后: " . $newDate->format('Y-m-d H:i:s') . 
                      " (年份: {$currentDate->format('Y')} -> {$newDate->format('Y')})");
            $currentDate = $newDate;
        }

        // 测试金融计算
        $io->section('金融计算测试');
        $balance = '1000.00';
        $quarterlyPrice = '79.90'; // 季度会员价格
        
        $io->text('余额: ' . $balance);
        $io->text('季度会员价格: ' . $quarterlyPrice);
        
        if ($this->financialCalculator->isSufficient($balance, $quarterlyPrice)) {
            $newBalance = $this->financialCalculator->subtract($balance, $quarterlyPrice);
            $io->success('余额足够支付季度会员');
            $io->text('新余额: ' . $newBalance);
        } else {
            $io->error('余额不足');
        }

        $io->success('所有测试完成！');
        return Command::SUCCESS;
    }
}