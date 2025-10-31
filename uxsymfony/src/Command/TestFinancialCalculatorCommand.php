<?php

namespace App\Command;

use App\Service\FinancialCalculatorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-financial-calculator',
    description: '测试金融计算器服务',
)]
class TestFinancialCalculatorCommand extends Command
{
    private FinancialCalculatorService $financialCalculator;

    public function __construct(FinancialCalculatorService $financialCalculator)
    {
        parent::__construct();
        $this->financialCalculator = $financialCalculator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('金融计算器服务测试');

        // 测试加法
        $result = $this->financialCalculator->add('0.1', '0.2');
        $io->text("0.1 + 0.2 = {$result}");

        // 测试减法
        $result = $this->financialCalculator->subtract('1.0', '0.9');
        $io->text("1.0 - 0.9 = {$result}");

        // 测试乘法
        $result = $this->financialCalculator->multiply('0.1', '0.2');
        $io->text("0.1 * 0.2 = {$result}");

        // 测试除法
        $result = $this->financialCalculator->divide('1.0', '3.0', 10);
        $io->text("1.0 / 3.0 = {$result}");

        // 测试比较
        $result = $this->financialCalculator->compare('0.1', '0.2');
        $io->text("0.1 与 0.2 比较结果: {$result}");

        // 测试百分比计算
        $result = $this->financialCalculator->calculatePercentage('100.00', '15.5');
        $io->text("100.00 的 15.5% = {$result}");

        // 测试余额是否足够
        $isSufficient = $this->financialCalculator->isSufficient('100.00', '99.99');
        $io->text("余额 100.00 是否足够支付 99.99: " . ($isSufficient ? '是' : '否'));

        $isSufficient = $this->financialCalculator->isSufficient('100.00', '100.01');
        $io->text("余额 100.00 是否足够支付 100.01: " . ($isSufficient ? '是' : '否'));

        $io->success('金融计算器服务测试完成!');

        return Command::SUCCESS;
    }
}