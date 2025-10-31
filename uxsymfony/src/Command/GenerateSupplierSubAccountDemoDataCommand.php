<?php

namespace App\Command;

use App\Entity\Supplier;
use App\Entity\SupplierSubAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GenerateSupplierSubAccountDemoDataCommand extends Command
{
    protected static $defaultName = 'app:generate-supplier-subaccount-demo-data';
    
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:generate-supplier-subaccount-demo-data')
            ->setDescription('为指定供应商生成子账号演示数据')
            ->addOption('supplier-username', null, InputOption::VALUE_REQUIRED, '指定供应商用户名');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->info('开始为供应商生成子账号演示数据...');

        // 获取指定的供应商用户名
        $supplierUsername = $input->getOption('supplier-username');
        
        if (!$supplierUsername) {
            $io->error('请指定供应商用户名，使用 --supplier-username 参数');
            return Command::FAILURE;
        }

        // 开始事务
        $this->entityManager->beginTransaction();
        try {
            // 获取指定的供应商
            $supplier = $this->entityManager->getRepository(Supplier::class)->findOneBy(['username' => $supplierUsername]);
            
            if (!$supplier) {
                $io->error("没有找到用户名为 '{$supplierUsername}' 的供应商");
                $this->entityManager->rollback();
                return Command::FAILURE;
            }
            
            $totalSubAccounts = 0;
            
            // 检查当前供应商已有的子账号数量
            $existingCount = $this->entityManager->getRepository(SupplierSubAccount::class)
                ->count(['supplier' => $supplier]);
            
            // 计算还需要生成多少个子账号（最多7个）
            $needToCreate = 7 - $existingCount;
            
            // 检查当前供应商已有的子账号数量
            $existingCount = $this->entityManager->getRepository(SupplierSubAccount::class)
                ->count(['supplier' => $supplier]);
            
            // 计算还需要生成多少个子账号（最多7个）
            $needToCreate = 7 - $existingCount;
            
            if ($needToCreate <= 0) {
                $io->text("供应商 {$supplier->getUsername()} 已有 {$existingCount} 个子账号，无需生成");
            } else {
                $io->text("为供应商 {$supplier->getUsername()} 生成 {$needToCreate} 个子账号...");
                
                // 生成子账号
                for ($i = 1; $i <= $needToCreate; $i++) {
                    $subAccount = new SupplierSubAccount();
                    
                    // 生成唯一的用户名和邮箱
                    $suffix = $existingCount + $i;
                    $username = $supplier->getUsername() . "_sub{$suffix}";
                    $email = $supplier->getUsername() . "_sub{$suffix}@demo.com";
                    
                    // 检查用户名和邮箱是否已存在
                    $existingSubAccount = $this->entityManager->getRepository(SupplierSubAccount::class)
                        ->findOneBy(['username' => $username]);
                    if ($existingSubAccount) {
                        $username = $username . '_' . uniqid();
                    }
                    
                    $existingEmail = $this->entityManager->getRepository(SupplierSubAccount::class)
                        ->findOneBy(['email' => $email]);
                    if ($existingEmail) {
                        $email = str_replace('@', '_' . uniqid() . '@', $email);
                    }
                    
                    $subAccount->setUsername($username);
                    $subAccount->setEmail($email);
                    $subAccount->setPassword($this->passwordHasher->hashPassword($subAccount, '12345678'));
                    $subAccount->setMark("子账号{$suffix}");
                    $subAccount->setIsActive(true);
                    $subAccount->setSupplier($supplier);
                    $subAccount->setCreatedAt(new \DateTime());
                    
                    $this->entityManager->persist($subAccount);
                    $totalSubAccounts++;
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            $io->success("成功为 {$totalSubAccounts} 个子账号生成演示数据！");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $io->error('生成子账号演示数据失败: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
