<?php

namespace App\Command;

use App\Service\SiteConfigService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:initialize-membership-prices',
    description: '初始化会员价格配置',
)]
class InitializeMembershipPricesCommand extends Command
{

    private $siteConfigService;

    public function __construct(SiteConfigService $siteConfigService)
    {
        parent::__construct();
        $this->siteConfigService = $siteConfigService;
    }

    protected function configure(): void
    {
        // 描述已在 #[AsCommand] 注解中定义
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 默认会员价格配置
        $defaultPrices = [
            'monthly' => ['price' => '29.90', 'description' => '月会员价格'],
            'quarterly' => ['price' => '79.90', 'description' => '季度会员价格'],
            'half_yearly' => ['price' => '139.90', 'description' => '半年会员价格'],
            'yearly' => ['price' => '249.90', 'description' => '年会员价格'],
        ];

        foreach ($defaultPrices as $type => $config) {
            $key = 'mvip_price_' . $type;
            $existingValue = $this->siteConfigService->getConfigValue($key);
            
            if ($existingValue === null) {
                $this->siteConfigService->setConfigValue($key, $config['price'], $config['description']);
                $io->success("已设置 {$type} 会员价格为 {$config['price']}");
            } else {
                $io->info("{$type} 会员价格已存在: {$existingValue}");
            }
        }

        // 测试获取会员价格
        $io->section('测试会员价格获取');
        $prices = $this->siteConfigService->getAllMembershipPrices();
        foreach ($prices as $type => $price) {
            $io->text("{$type}: {$price}");
        }

        $io->success('会员价格配置初始化完成!');

        return Command::SUCCESS;
    }
}