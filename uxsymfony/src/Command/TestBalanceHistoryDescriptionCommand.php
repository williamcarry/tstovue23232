<?php

namespace App\Command;

use App\Entity\BalanceHistory;
use App\Entity\Supplier;
use App\Service\FinancialCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestBalanceHistoryDescriptionCommand extends Command
{
    protected static $defaultName = 'app:test-balance-history-description';
    
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
            ->setName('app:test-balance-history-description')
            ->setDescription('测试余额记录描述显示');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('余额记录描述测试');

        // 创建测试供应商
        $supplier = new Supplier();
        $supplier->setUsername('test_balance_' . time());
        $supplier->setEmail('test_balance_' . time() . '@example.com');
        $supplier->setPassword('test_password');
        $supplier->setContactPerson('Test Balance');
        $supplier->setContactPhone('12345678901');
        $supplier->setBalance('1000.00'); // 设置初始余额1000元

        $this->entityManager->persist($supplier);
        $this->entityManager->flush();

        $io->success('创建测试供应商，ID: ' . $supplier->getId());

        // 创建不同类型的余额记录来测试描述
        $io->section('创建不同类型的余额记录');

        // 1. 购买VIP记录
        $balanceHistory1 = new BalanceHistory();
        $balanceHistory1->setUserType('supplier');
        $balanceHistory1->setUserId($supplier->getId());
        $balanceHistory1->setBalanceBefore('1000.00');
        $balanceHistory1->setBalanceAfter('970.10');
        $balanceHistory1->setAmount('-29.90');
        $balanceHistory1->setFrozenBalanceBefore('0.00');
        $balanceHistory1->setFrozenBalanceAfter('0.00');
        $balanceHistory1->setFrozenAmount('0.00');
        $balanceHistory1->setType('vip_purchase');
        $balanceHistory1->setDescription('购买月会员');
        $balanceHistory1->setCreatedAt(new \DateTime());

        $this->entityManager->persist($balanceHistory1);

        // 2. 提现记录
        $balanceHistory2 = new BalanceHistory();
        $balanceHistory2->setUserType('supplier');
        $balanceHistory2->setUserId($supplier->getId());
        $balanceHistory2->setBalanceBefore('970.10');
        $balanceHistory2->setBalanceAfter('870.10');
        $balanceHistory2->setAmount('-100.00');
        $balanceHistory2->setFrozenBalanceBefore('0.00');
        $balanceHistory2->setFrozenBalanceAfter('0.00');
        $balanceHistory2->setFrozenAmount('0.00');
        $balanceHistory2->setType('withdraw');
        $balanceHistory2->setDescription('申请提现100元');
        $balanceHistory2->setCreatedAt(new \DateTime());

        $this->entityManager->persist($balanceHistory2);

        // 3. 后台增加余额记录
        $balanceHistory3 = new BalanceHistory();
        $balanceHistory3->setUserType('supplier');
        $balanceHistory3->setUserId($supplier->getId());
        $balanceHistory3->setBalanceBefore('870.10');
        $balanceHistory3->setBalanceAfter('970.10');
        $balanceHistory3->setAmount('100.00');
        $balanceHistory3->setFrozenBalanceBefore('0.00');
        $balanceHistory3->setFrozenBalanceAfter('0.00');
        $balanceHistory3->setFrozenAmount('0.00');
        $balanceHistory3->setType('admin_add');
        $balanceHistory3->setDescription('后台增加余额100元');
        $balanceHistory3->setCreatedAt(new \DateTime());

        $this->entityManager->persist($balanceHistory3);

        // 4. 没有描述的记录
        $balanceHistory4 = new BalanceHistory();
        $balanceHistory4->setUserType('supplier');
        $balanceHistory4->setUserId($supplier->getId());
        $balanceHistory4->setBalanceBefore('970.10');
        $balanceHistory4->setBalanceAfter('940.20');
        $balanceHistory4->setAmount('-29.90');
        $balanceHistory4->setFrozenBalanceBefore('0.00');
        $balanceHistory4->setFrozenBalanceAfter('0.00');
        $balanceHistory4->setFrozenAmount('0.00');
        $balanceHistory4->setType('vip_purchase');
        $balanceHistory4->setDescription(null); // 没有描述
        $balanceHistory4->setCreatedAt(new \DateTime());

        $this->entityManager->persist($balanceHistory4);

        $this->entityManager->flush();

        $io->success('创建了4条测试余额记录');

        // 显示创建的记录
        $io->section('创建的余额记录详情');

        $repository = $this->entityManager->getRepository(BalanceHistory::class);
        $histories = $repository->findBy(['userId' => $supplier->getId()], ['createdAt' => 'ASC']);

        foreach ($histories as $index => $history) {
            $io->text("记录 " . ($index + 1) . ":");
            $io->text("  类型: " . $history->getType());
            $io->text("  描述: " . ($history->getDescription() ?: '无描述'));
            $io->text("  金额: " . $history->getAmount());
            $io->text("  时间: " . $history->getCreatedAt()->format('Y-m-d H:i:s'));
            $io->newLine();
        }

        // 测试类型描述映射
        $io->section('类型描述映射测试');
        $balanceHistory = new BalanceHistory();
        $typeDescriptions = $balanceHistory->getTypeDescriptions();
        
        foreach ($typeDescriptions as $type => $description) {
            $io->text("类型: {$type} => 描述: {$description}");
        }

        $io->success('所有测试完成！');
        return Command::SUCCESS;
    }
}