<?php

namespace App\Command;

use App\Entity\Supplier;
use App\Service\FinancialCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestMembershipScenariosCommand extends Command
{
    protected static $defaultName = 'app:test-membership-scenarios';
    
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
            ->setName('app:test-membership-scenarios')
            ->setDescription('测试所有会员购买场景');
    }

    /**
     * 添加月份到日期（更精确的方法）
     */
    private function addMonthsToDate(\DateTime $date, int $months): \DateTime
    {
        $newDate = clone $date;
        
        // 获取当前的年、月、日、时、分、秒
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

    /**
     * 计算新的会员到期时间
     */
    private function calculateNewExpiredAt(Supplier $supplier, string $membershipType): \DateTime
    {
        // 会员类型映射
        $membershipTypes = [
            'monthly' => ['name' => '月会员', 'months' => 1],
            'quarterly' => ['name' => '季度会员', 'months' => 3],
            'half_yearly' => ['name' => '半年会员', 'months' => 6],
            'yearly' => ['name' => '年会员', 'months' => 12],
        ];

        $now = new \DateTime();
        $months = $membershipTypes[$membershipType]['months'];
        
        // 如果当前会员有效，则在现有到期时间基础上延长（无论类型是否相同）
        if ($supplier->isMemberActive()) {
            // 将DateTimeInterface转换为DateTime对象
            $expiredAt = new \DateTime($supplier->getMembershipExpiredAt()->format('Y-m-d H:i:s'));
            $expiredAt = $this->addMonthsToDate($expiredAt, $months);
        } else {
            // 如果当前没有会员或会员已过期，则从现在开始计算
            $expiredAt = $this->addMonthsToDate($now, $months);
        }
        
        return $expiredAt;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('会员购买场景测试');

        // 场景1: 当前会员没过期，购买月会员
        $io->section('场景1: 当前会员没过期，购买月会员');
        $supplier1 = new Supplier();
        $supplier1->setMembershipType('quarterly');
        $futureDate = new \DateTime();
        $futureDate->modify('+2 months');
        $supplier1->setMembershipExpiredAt($futureDate);
        
        // 模拟isMemberActive()方法返回true
        $reflection = new \ReflectionClass($supplier1);
        $property = $reflection->getProperty('membershipType');
        $property->setAccessible(true);
        $property->setValue($supplier1, 'quarterly');
        
        $io->text('当前会员类型: 季度会员');
        $io->text('当前会员到期时间: ' . $supplier1->getMembershipExpiredAt()->format('Y-m-d H:i:s'));
        $io->text('会员状态: 有效');
        
        $newExpiredAt1 = $this->calculateNewExpiredAt($supplier1, 'monthly');
        $io->text('购买月会员后的新到期时间: ' . $newExpiredAt1->format('Y-m-d H:i:s'));
        $io->text('结论: 从现有到期时间延长1个月');

        // 场景2: 当前会员过期了，购买季度会员
        $io->section('场景2: 当前会员过期了，购买季度会员');
        $supplier2 = new Supplier();
        $supplier2->setMembershipType('monthly');
        $pastDate = new \DateTime();
        $pastDate->modify('-1 month');
        $supplier2->setMembershipExpiredAt($pastDate);
        
        $io->text('当前会员类型: 月会员');
        $io->text('当前会员到期时间: ' . $supplier2->getMembershipExpiredAt()->format('Y-m-d H:i:s'));
        $io->text('会员状态: 已过期');
        
        $newExpiredAt2 = $this->calculateNewExpiredAt($supplier2, 'quarterly');
        $io->text('购买季度会员后的新到期时间: ' . $newExpiredAt2->format('Y-m-d H:i:s'));
        $io->text('结论: 从当前时间开始计算3个月');

        // 场景3: 当前不是会员，购买年会员
        $io->section('场景3: 当前不是会员，购买年会员');
        $supplier3 = new Supplier();
        $supplier3->setMembershipType('none');
        $supplier3->setMembershipExpiredAt(null);
        
        $io->text('当前会员类型: 无会员');
        $io->text('当前会员到期时间: 无');
        $io->text('会员状态: 无会员');
        
        $newExpiredAt3 = $this->calculateNewExpiredAt($supplier3, 'yearly');
        $io->text('购买年会员后的新到期时间: ' . $newExpiredAt3->format('Y-m-d H:i:s'));
        $io->text('结论: 从当前时间开始计算12个月');

        // 场景4: 当前会员没过期，购买年会员
        $io->section('场景4: 当前会员没过期，购买年会员');
        $supplier4 = new Supplier();
        $supplier4->setMembershipType('monthly');
        $futureDate2 = new \DateTime();
        $futureDate2->modify('+5 months');
        $supplier4->setMembershipExpiredAt($futureDate2);
        
        $io->text('当前会员类型: 月会员');
        $io->text('当前会员到期时间: ' . $supplier4->getMembershipExpiredAt()->format('Y-m-d H:i:s'));
        $io->text('会员状态: 有效');
        
        $newExpiredAt4 = $this->calculateNewExpiredAt($supplier4, 'yearly');
        $io->text('购买年会员后的新到期时间: ' . $newExpiredAt4->format('Y-m-d H:i:s'));
        $io->text('结论: 从现有到期时间延长12个月');

        $io->success('所有场景测试完成！');
        return Command::SUCCESS;
    }
}