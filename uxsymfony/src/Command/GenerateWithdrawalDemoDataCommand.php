<?php

namespace App\Command;

use App\Entity\Supplier;
use App\Entity\Withdrawal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-withdrawal-demo-data',
    description: '生成提现申请演示数据',
)]
class GenerateWithdrawalDemoDataCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addOption('count', 'c', InputOption::VALUE_OPTIONAL, '生成的数据条数', 30)
            ->addOption('supplier-count', 's', InputOption::VALUE_OPTIONAL, '使用的供应商数量', 5)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = (int) $input->getOption('count');
        $supplierCount = (int) $input->getOption('supplier-count');

        // 获取供应商列表
        $suppliers = $this->entityManager->getRepository(Supplier::class)
            ->findBy([], null, $supplierCount);

        if (empty($suppliers)) {
            $io->error('没有找到供应商，请先创建供应商数据');
            return Command::FAILURE;
        }

        // 获取所有供应商ID
        $supplierIds = [];
        foreach ($suppliers as $supplier) {
            $supplierIds[] = $supplier->getId();
        }

        $io->note(sprintf('开始生成 %d 条提现申请演示数据，使用供应商ID: %s', $count, implode(', ', $supplierIds)));

        // 启动进度条
        $io->progressStart($count);

        // 状态选项
        $statuses = ['pending', 'approved', 'rejected'];
        $statusTexts = [
            'pending' => '待审核',
            'approved' => '已通过',
            'rejected' => '已拒绝'
        ];

        // 审核人选项
        $reviewers = ['admin', 'finance_manager', 'auditor'];

        // 生成提现申请数据
        for ($i = 0; $i < $count; $i++) {
            $withdrawal = new Withdrawal();
            
            // 随机选择供应商
            $supplier = $suppliers[array_rand($suppliers)];
            $withdrawal->setSupplierId($supplier->getId());
            
            // 随机生成提现金额 (100-10000元)
            $amount = mt_rand(100, 10000) + (mt_rand(0, 99) / 100);
            $withdrawal->setAmount(number_format($amount, 2, '.', ''));
            
            // 随机选择状态
            $status = $statuses[array_rand($statuses)];
            $withdrawal->setStatus($status);
            
            // 添加备注
            $withdrawal->setRemark(sprintf('提现申请备注信息 - %s', $statusTexts[$status]));
            
            // 如果状态不是待审核，设置审核人和审核时间
            if ($status !== 'pending') {
                $reviewer = $reviewers[array_rand($reviewers)];
                $withdrawal->setReviewedBy($reviewer);
                
                // 设置审核时间为申请时间之后的随机时间 (1-30天)
                $reviewedAt = \DateTime::createFromInterface($withdrawal->getCreatedAt());
                $days = mt_rand(1, 30);
                $reviewedAt->modify("+{$days} days");
                $withdrawal->setReviewedAt($reviewedAt);
            }
            
            // 设置更新时间
            $updatedAt = \DateTime::createFromInterface($withdrawal->getCreatedAt());
            // 如果已审核，更新时间为审核时间之后
            if ($withdrawal->getReviewedAt()) {
                $updatedAt = \DateTime::createFromInterface($withdrawal->getReviewedAt());
                $hours = mt_rand(1, 24);
                $updatedAt->modify("+{$hours} hours");
            } else {
                // 否则为申请时间之后的随机时间
                $hours = mt_rand(1, 24);
                $updatedAt->modify("+{$hours} hours");
            }
            $withdrawal->setUpdatedAt($updatedAt);
            
            $this->entityManager->persist($withdrawal);
            
            // 每10条数据刷新一次，避免内存问题
            if (($i + 1) % 10 === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $io->progressAdvance(10);
            }
        }
        
        // 刷新剩余数据
        $this->entityManager->flush();
        $this->entityManager->clear();
        
        // 完成进度条
        $io->progressFinish();
        
        $io->success(sprintf('成功生成 %d 条提现申请演示数据！', $count));
        
        return Command::SUCCESS;
    }
}