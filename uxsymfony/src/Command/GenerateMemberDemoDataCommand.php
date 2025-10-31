<?php

namespace App\Command;

use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GenerateMemberDemoDataCommand extends Command
{
    protected static $defaultName = 'app:generate-member-demo-data';
    
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:generate-member-demo-data')
            ->setDescription('生成会员演示数据');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('开始生成会员演示数据');
        
        // 生成30个会员演示数据
        $createdCount = 0;
        for ($i = 1; $i <= 30; $i++) {
            $customer = new Customer();
            
            // 生成用户名
            $username = 'member' . str_pad($i, 3, '0', STR_PAD_LEFT);
            
            // 检查用户名是否已存在
            $existingCustomer = $this->entityManager->getRepository(Customer::class)->findOneBy(['username' => $username]);
            if ($existingCustomer) {
                $io->note("会员 {$username} 已存在，跳过");
                continue;
            }
            
            // 设置基本信息
            $customer->setUsername($username);
            $customer->setEmail("member{$i}@example.com");
            $customer->setPassword($this->passwordHasher->hashPassword($customer, '12345678'));
            
            // 随机设置昵称和真实姓名
            $nicknames = ['小明', '小红', '小刚', '小丽', '小强', '小芳', '小伟', '小敏', '小军', '小霞'];
            $customer->setNickname($nicknames[array_rand($nicknames)] . $i);
            
            $realNames = ['张三', '李四', '王五', '赵六', '钱七', '孙八', '周九', '吴十'];
            $customer->setRealName($realNames[array_rand($realNames)] . $i);
            
            // 设置手机号
            $customer->setMobile('138' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT));
            
            // 随机设置性别 (0:未知, 1:男, 2:女)
            $customer->setGender(rand(0, 2));
            
            // 随机设置账号状态 (80%概率激活)
            $customer->setIsActive(rand(1, 100) <= 80);
            
            // 随机设置VIP等级 (0-6, 70%概率为普通用户)
            $vipLevels = [0, 0, 0, 0, 0, 0, 0, 1, 1, 2, 2, 3, 4, 5, 6];
            $customer->setVipLevel($vipLevels[array_rand($vipLevels)]);
            
            // 随机设置账户余额
            $customer->setBalance(rand(0, 1000000) / 100);
            
            // 保存到数据库
            $this->entityManager->persist($customer);
            $createdCount++;
            
            $io->text("已创建会员: {$username}");
        }
        
        // 刷新数据库
        $this->entityManager->flush();
        
        $io->success("会员演示数据生成完成! 共创建 {$createdCount} 个新会员。");
        
        return Command::SUCCESS;
    }
}