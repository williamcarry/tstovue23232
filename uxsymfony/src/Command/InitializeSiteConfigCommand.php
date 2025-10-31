<?php

namespace App\Command;

use App\Service\SiteConfigService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:initialize-site-config',
    description: '初始化网站配置参数',
)]
class InitializeSiteConfigCommand extends Command
{
    private $siteConfigService;

    public function __construct(SiteConfigService $siteConfigService)
    {
        parent::__construct();
        $this->siteConfigService = $siteConfigService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 初始化一些常用的网站配置参数
        $configs = [
            'site_name' => ['value' => 'SaleYee商城系统', 'description' => '网站名称'],
            'site_description' => ['value' => '专业的B2B电商平台', 'description' => '网站描述'],
            'site_keywords' => ['value' => 'B2B,电商,供应商', 'description' => '网站关键词'],
            'site_logo' => ['value' => '', 'description' => '网站Logo路径'],
            'site_favicon' => ['value' => '', 'description' => '网站Favicon路径'],
            'contact_email' => ['value' => 'admin@example.com', 'description' => '联系邮箱'],
            'contact_phone' => ['value' => '400-123-4567', 'description' => '联系电话'],
            'icp_number' => ['value' => '京ICP备12345678号', 'description' => 'ICP备案号'],
            'copyright' => ['value' => '© 2025 SaleYee 版权所有', 'description' => '版权信息'],
            'enable_registration' => ['value' => '1', 'description' => '是否允许注册（1-允许，0-不允许）'],
            'enable_supplier_registration' => ['value' => '1', 'description' => '是否允许供应商注册（1-允许，0-不允许）'],
        ];

        foreach ($configs as $key => $config) {
            $this->siteConfigService->setConfigValue($key, $config['value'], $config['description']);
        }

        $io->success('网站配置初始化完成！');
        $io->note('已初始化以下配置项：');
        foreach ($configs as $key => $config) {
            $io->text("- {$key}: {$config['value']}");
        }

        return Command::SUCCESS;
    }
}