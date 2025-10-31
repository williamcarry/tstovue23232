<?php

namespace App\Command;

use App\Entity\Supplier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateSupplierFinancialDataCommand extends Command
{
    protected static $defaultName = 'app:generate-supplier-financial-data';
    
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:generate-supplier-financial-data')
            ->setDescription('为现有供应商生成财务和会员演示数据');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->info('开始为现有供应商生成财务和会员演示数据...');

        // 开始事务
        $this->entityManager->beginTransaction();
        try {
            // 获取所有供应商
            $suppliers = $this->entityManager->getRepository(Supplier::class)->findAll();
            
            if (empty($suppliers)) {
                $io->warning('没有找到任何供应商，请先生成供应商演示数据');
                return Command::FAILURE;
            }
            
            $updatedCount = 0;
            
            foreach ($suppliers as $supplier) {
                // 只为还没有财务数据的供应商生成演示数据
                if ($supplier->getBalance() === null && 
                    $supplier->getBalanceFrozen() === null && 
                    $supplier->getMembershipType() === null) {
                    
                    // 生成余额数据
                    $balance = rand(1000, 100000);
                    $supplier->setBalance($balance);
                    
                    // 生成冻结余额数据（通常小于余额）
                    $frozenBalance = rand(0, $balance * 0.3);
                    $supplier->setBalanceFrozen($frozenBalance);
                    
                    // 生成会员类型数据
                    $membershipTypes = ['none', 'monthly', 'quarterly', 'half_yearly', 'yearly'];
                    $membershipType = $membershipTypes[array_rand($membershipTypes)];
                    $supplier->setMembershipType($membershipType);
                    
                    // 生成会员到期时间（如果会员类型不是none）
                    if ($membershipType !== 'none') {
                        $daysToAdd = match($membershipType) {
                            'monthly' => 30,
                            'quarterly' => 90,
                            'half_yearly' => 180,
                            'yearly' => 365,
                            default => 30
                        };
                        
                        $expireDate = new \DateTime();
                        $expireDate->modify("+{$daysToAdd} days");
                        $supplier->setMembershipExpiredAt($expireDate);
                    }
                    
                    // 生成佣金比例数据（30%概率使用自定义佣金比例）
                    if (rand(1, 100) <= 30) {
                        // 生成0.0001到0.1500之间的佣金比例（0.01%到15%）
                        $commissionRate = rand(1, 1500) / 10000;
                        $supplier->setCommissionRate(number_format($commissionRate, 4, '.', ''));
                    }
                    
                    $updatedCount++;
                }
            }
            
            $this->entityManager->flush();
            $this->entityManager->commit();
            
            $io->success("成功为 {$updatedCount} 个供应商生成了财务和会员演示数据！");
            $io->note("数据包括：");
            $io->text("- 余额（balance）：1000-100000元");
            $io->text("- 冻结余额（balance_frozen）：0-余额的30%");
            $io->text("- 会员类型（membership_type）：none, monthly, quarterly, half_yearly, yearly");
            $io->text("- 会员到期时间（membership_expired_at）：根据会员类型设置");
            $io->text("- 佣金比例（commission_rate）：30%的供应商有0.01%-15%的自定义佣金比例");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $io->error('生成演示数据失败: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}