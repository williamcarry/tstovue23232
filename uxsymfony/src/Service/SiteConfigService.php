<?php

namespace App\Service;

use App\Entity\SiteConfig;
use App\Repository\SiteConfigRepository;
use Doctrine\ORM\EntityManagerInterface;

class SiteConfigService
{
    private $siteConfigRepository;
    private $entityManager;

    public function __construct(
        SiteConfigRepository $siteConfigRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->siteConfigRepository = $siteConfigRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * 根据键名获取配置值
     *
     * @param string $key 配置键名
     * @return string|null
     */
    public function getConfigValue(string $key): ?string
    {
        $config = $this->siteConfigRepository->findOneByKey($key);
        return $config ? $config->getConfigValue() : null;
    }

    /**
     * 设置配置值
     *
     * @param string $key 配置键名
     * @param string|null $value 配置值
     * @param string|null $description 配置描述
     * @return SiteConfig
     */
    public function setConfigValue(string $key, ?string $value, ?string $description = null): SiteConfig
    {
        return $this->siteConfigRepository->updateOrCreateConfig($key, $value, $description);
    }

    /**
     * 获取所有配置项
     *
     * @return SiteConfig[]
     */
    public function getAllConfig(): array
    {
        return $this->siteConfigRepository->findAll();
    }

    /**
     * 删除配置项
     *
     * @param string $key 配置键名
     * @return bool
     */
    public function deleteConfig(string $key): bool
    {
        $config = $this->siteConfigRepository->findOneByKey($key);
        if ($config) {
            $this->entityManager->remove($config);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    /**
     * 获取网站通用佣金比例
     * 
     * @return string|null 佣金比例（小数形式，如'0.1000'表示10%）
     */
    public function getSiteCommissionRate(): ?string
    {
        $configValue = $this->getConfigValue('commission_rate');
        if ($configValue !== null && is_numeric($configValue)) {
            // 确保精度为4位小数
            return number_format((float) $configValue, 4, '.', '');
        }
        return null;
    }

    /**
     * 获取会员价格配置
     * 
     * @param string $membershipType 会员类型
     * @return string|null 会员价格
     */
    public function getMembershipPrice(string $membershipType): ?string
    {
        $configKey = 'mvip_price_' . $membershipType;
        return $this->getConfigValue($configKey);
    }

    /**
     * 获取所有会员价格配置
     * 
     * @return array 会员价格配置数组
     */
    public function getAllMembershipPrices(): array
    {
        $membershipTypes = ['monthly', 'quarterly', 'half_yearly', 'yearly'];
        $prices = [];
        
        foreach ($membershipTypes as $type) {
            $price = $this->getMembershipPrice($type);
            if ($price !== null) {
                $prices[$type] = $price;
            }
        }
        
        return $prices;
    }
}