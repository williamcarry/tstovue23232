<?php

namespace App\Command;

use App\Entity\BalanceHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateBalanceHistoryDemoCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct('app:generate-balance-history-demo');
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('生成35条余额变化记录的demo数据');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 定义用户类型和ID
        $users = [
            // 供应商用户
            ['type' => 'supplier', 'id' => 1],
            ['type' => 'supplier', 'id' => 11],
            ['type' => 'supplier', 'id' => 12],
            ['type' => 'supplier', 'id' => 34],
            ['type' => 'supplier', 'id' => 35],
            // 客户用户
            ['type' => 'customer', 'id' => 1],
            ['type' => 'customer', 'id' => 2],
            ['type' => 'customer', 'id' => 3],
            ['type' => 'customer', 'id' => 4],
            ['type' => 'customer', 'id' => 31],
        ];

        // 定义业务类型
        $types = [
            'recharge' => '充值',
            'withdraw' => '提现',
            'order' => '订单',
            'commission' => '佣金',
            'refund' => '退款',
            'system' => '系统调整',
            'vip_purchase' => '购买VIP',
            'admin_add' => '后台增加余额',
            'admin_deduct' => '后台减少余额'
        ];

        // 定义类型说明
        $typeDescriptions = [
            'recharge' => '充值',
            'withdraw' => '提现',
            'order' => '订单',
            'commission' => '佣金',
            'refund' => '退款',
            'system' => '系统调整',
            'vip_purchase' => '购买VIP',
            'admin_add' => '后台增加余额',
            'admin_deduct' => '后台减少余额'
        ];

        $io->writeln("开始生成35条余额变化记录demo数据...");

        // 生成35条记录
        for ($i = 1; $i <= 35; $i++) {
            // 随机选择用户
            $user = $users[array_rand($users)];
            
            // 随机选择业务类型
            $typeKeys = array_keys($types);
            $type = $typeKeys[array_rand($typeKeys)];
            
            // 生成随机金额
            $balanceBefore = rand(0, 10000) / 100;
            $amount = (rand(-5000, 5000) / 100);
            $balanceAfter = $balanceBefore + $amount;
            $frozenBalanceBefore = rand(0, 1000) / 100;
            $frozenAmount = (rand(-500, 500) / 100);
            $frozenBalanceAfter = $frozenBalanceBefore + $frozenAmount;
            
            // 创建余额变化记录
            $balanceHistory = new BalanceHistory();
            $balanceHistory->setUserType($user['type']);
            $balanceHistory->setUserId($user['id']);
            $balanceHistory->setBalanceBefore(number_format($balanceBefore, 2, '.', ''));
            $balanceHistory->setBalanceAfter(number_format($balanceAfter, 2, '.', ''));
            $balanceHistory->setAmount(number_format($amount, 2, '.', ''));
            $balanceHistory->setFrozenBalanceBefore(number_format($frozenBalanceBefore, 2, '.', ''));
            $balanceHistory->setFrozenBalanceAfter(number_format($frozenBalanceAfter, 2, '.', ''));
            $balanceHistory->setFrozenAmount(number_format($frozenAmount, 2, '.', ''));
            $balanceHistory->setType($type);
            $balanceHistory->setDescription("测试记录 #" . $i . " - " . $types[$type]);
            $balanceHistory->setReferenceId("REF" . str_pad($i, 6, "0", STR_PAD_LEFT));
            $balanceHistory->setTypeDescriptions($typeDescriptions);
            
            // 保存到数据库
            $this->entityManager->persist($balanceHistory);
            
            $io->writeln("创建记录 #" . $i . " - 用户: " . $user['type'] . " #" . $user['id'] . ", 类型: " . $types[$type] . ", 金额: " . number_format($amount, 2));
        }

        // 提交所有更改
        $this->entityManager->flush();

        $io->success("成功生成35条余额变化记录demo数据！");

        return Command::SUCCESS;
    }
}