<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetSupplierToNoMembershipCommand extends Command
{
    protected static $defaultName = 'app:set-supplier-to-no-membership';
    
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:set-supplier-to-no-membership')
            ->setDescription('将供应商设置为无会员状态')
            ->addOption('supplier-id', null, InputOption::VALUE_REQUIRED, '供应商ID')
            ->addOption('all', null, InputOption::VALUE_NONE, '将所有供应商设置为无会员状态');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $supplierId = $input->getOption('supplier-id');
        $all = $input->getOption('all');
        
        if (!$supplierId && !$all) {
            $io->error('请提供 --supplier-id 或 --all 选项');
            return Command::FAILURE;
        }
        
        $connection = $this->entityManager->getConnection();
        
        try {
            if ($all) {
                // 将所有供应商设置为无会员状态
                $sql = "UPDATE supplier SET membership_type = 'none', membership_expired_at = NULL, updated_at = NOW()";
                $stmt = $connection->prepare($sql);
                $result = $stmt->executeStatement();
                
                $io->success("已将 {$result} 个供应商设置为无会员状态");
            } else {
                // 将指定供应商设置为无会员状态
                $sql = "UPDATE supplier SET membership_type = 'none', membership_expired_at = NULL, updated_at = NOW() WHERE id = ?";
                $stmt = $connection->prepare($sql);
                $result = $stmt->executeStatement([$supplierId]);
                
                if ($result > 0) {
                    $io->success("已将供应商 ID {$supplierId} 设置为无会员状态");
                } else {
                    $io->warning("未找到供应商 ID {$supplierId}");
                    return Command::FAILURE;
                }
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('更新失败: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}