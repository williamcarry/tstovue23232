<?php

namespace App\Service;

use App\Entity\Supplier;

class CommissionService
{
    private SiteConfigService $siteConfigService;

    public function __construct(SiteConfigService $siteConfigService)
    {
        $this->siteConfigService = $siteConfigService;
    }

    /**
     * 获取供应商的有效佣金比例
     * 
     * @param Supplier $supplier 供应商实体
     * @return string|null 佣金比例（小数形式，如'0.1000'表示10%）
     */
    public function getEffectiveCommissionRate(Supplier $supplier): ?string
    {
        // 使用闭包作为回调函数来获取网站配置
        $siteConfigCallback = function() {
            return $this->siteConfigService->getSiteCommissionRate();
        };
        
        return $supplier->getEffectiveCommissionRate($siteConfigCallback);
    }

    /**
     * 获取供应商的有效佣金比例（百分比形式）
     * 
     * @param Supplier $supplier 供应商实体
     * @return float|null 佣金比例（百分比形式，如10表示10%）
     */
    public function getEffectiveCommissionRatePercentage(Supplier $supplier): ?float
    {
        $rate = $this->getEffectiveCommissionRate($supplier);
        return $rate !== null ? (float) bcmul($rate, '100', 2) : null;
    }
}