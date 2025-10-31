<?php

namespace App\Command;

use App\Service\FinancialCalculatorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestDateCalculationCommand extends Command
{
    protected static $defaultName = 'app:test-date-calculation';
    
    private FinancialCalculatorService $financialCalculator;

    public function __construct(FinancialCalculatorService $financialCalculator)
    {
        parent::__construct();
        $this->financialCalculator = $financialCalculator;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:test-date-calculation')
            ->setDescription('测试日期计算功能');
    }

    /**
     * 添加月份到日期
     */
    private function addMonthsToDate(\DateTime $date, int $months): \DateTime
    {
        $newDate = clone $date;
        $years = floor($months / 12);
        $remainingMonths = $months % 12;
        
        // 先加年份
        if ($years > 0) {
            $newDate->modify('+' . $years . ' years');
        }
        
        // 再加月份
        if ($remainingMonths > 0) {
            $newDate->modify('+' . $remainingMonths . ' months');
        }
        
        return $newDate;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('日期计算测试');

        // 测试用例1: 从2025-10-30开始，加12个月
        $io->section('测试用例1: 从2025-10-30开始，加12个月');
        $startDate1 = new \DateTime('2025-10-30 10:00:00');
        $io->text('开始日期: ' . $startDate1->format('Y-m-d H:i:s'));
        
        $endDate1 = $this->addMonthsToDate($startDate1, 12);
        $io->text('结束日期: ' . $endDate1->format('Y-m-d H:i:s'));
        $io->text('年份变化: ' . $startDate1->format('Y') . ' -> ' . $endDate1->format('Y'));
        $io->text('月份变化: ' . $startDate1->format('m') . ' -> ' . $endDate1->format('m'));

        // 测试用例2: 从2025-01-31开始，加1个月
        $io->section('测试用例2: 从2025-01-31开始，加1个月');
        $startDate2 = new \DateTime('2025-01-31 10:00:00');
        $io->text('开始日期: ' . $startDate2->format('Y-m-d H:i:s'));
        
        $endDate2 = $this->addMonthsToDate($startDate2, 1);
        $io->text('结束日期: ' . $endDate2->format('Y-m-d H:i:s'));
        $io->text('年份变化: ' . $startDate2->format('Y') . ' -> ' . $endDate2->format('Y'));
        $io->text('月份变化: ' . $startDate2->format('m') . ' -> ' . $endDate2->format('m'));

        // 测试用例3: 从2025-12-15开始，加1个月
        $io->section('测试用例3: 从2025-12-15开始，加1个月');
        $startDate3 = new \DateTime('2025-12-15 10:00:00');
        $io->text('开始日期: ' . $startDate3->format('Y-m-d H:i:s'));
        
        $endDate3 = $this->addMonthsToDate($startDate3, 1);
        $io->text('结束日期: ' . $endDate3->format('Y-m-d H:i:s'));
        $io->text('年份变化: ' . $startDate3->format('Y') . ' -> ' . $endDate3->format('Y'));
        $io->text('月份变化: ' . $startDate3->format('m') . ' -> ' . $endDate3->format('m'));

        // 测试用例4: 从2025-10-30开始，加24个月（2年）
        $io->section('测试用例4: 从2025-10-30开始，加24个月（2年）');
        $startDate4 = new \DateTime('2025-10-30 10:00:00');
        $io->text('开始日期: ' . $startDate4->format('Y-m-d H:i:s'));
        
        $endDate4 = $this->addMonthsToDate($startDate4, 24);
        $io->text('结束日期: ' . $endDate4->format('Y-m-d H:i:s'));
        $io->text('年份变化: ' . $startDate4->format('Y') . ' -> ' . $endDate4->format('Y'));
        $io->text('月份变化: ' . $startDate4->format('m') . ' -> ' . $endDate4->format('m'));

        // 测试金融计算
        $io->section('金融计算测试');
        $balance = '1000.00';
        $price = '29.90';
        
        $io->text('余额: ' . $balance);
        $io->text('价格: ' . $price);
        
        if ($this->financialCalculator->isSufficient($balance, $price)) {
            $newBalance = $this->financialCalculator->subtract($balance, $price);
            $io->success('余额足够支付');
            $io->text('新余额: ' . $newBalance);
        } else {
            $io->error('余额不足');
        }

        $io->success('所有测试完成！');
        return Command::SUCCESS;
    }
}