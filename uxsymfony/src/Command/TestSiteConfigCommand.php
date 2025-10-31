<?php

namespace App\Command;

use App\Service\SiteConfigService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-site-config',
    description: '测试网站配置服务',
)]
class TestSiteConfigCommand extends Command
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

        // Test retrieving a configuration value
        $siteName = $this->siteConfigService->getConfigValue('site_name');
        $io->success("Site Name: " . $siteName);

        // Test setting a configuration value
        $this->siteConfigService->setConfigValue('test_config', 'test_value', '测试配置项');

        // Test retrieving the newly set configuration value
        $testConfig = $this->siteConfigService->getConfigValue('test_config');
        $io->success("Test Config: " . $testConfig);

        // List all configurations
        $io->section("All configurations:");
        $configs = $this->siteConfigService->getAllConfig();
        foreach ($configs as $config) {
            $io->text("- " . $config->getConfigKey() . ": " . $config->getConfigValue());
        }

        return Command::SUCCESS;
    }
}