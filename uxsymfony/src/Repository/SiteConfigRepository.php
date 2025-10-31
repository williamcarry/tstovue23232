<?php

namespace App\Repository;

use App\Entity\SiteConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SiteConfig>
 *
 * @method SiteConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method SiteConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method SiteConfig[]    findAll()
 * @method SiteConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteConfig::class);
    }

    /**
     * 根据配置键名查找配置项
     *
     * @param string $key 配置键名
     * @return SiteConfig|null
     */
    public function findOneByKey(string $key): ?SiteConfig
    {
        return $this->findOneBy(['configKey' => $key]);
    }

    /**
     * 更新配置项
     *
     * @param string $key 配置键名
     * @param string|null $value 配置值
     * @param string|null $description 配置描述
     * @return SiteConfig
     */
    public function updateOrCreateConfig(string $key, ?string $value, ?string $description = null): SiteConfig
    {
        $siteConfig = $this->findOneByKey($key);
        
        if (!$siteConfig) {
            $siteConfig = new SiteConfig();
            $siteConfig->setConfigKey($key);
        }
        
        $siteConfig->setConfigValue($value);
        if ($description !== null) {
            $siteConfig->setDescription($description);
        }
        
        $this->getEntityManager()->persist($siteConfig);
        $this->getEntityManager()->flush();
        
        return $siteConfig;
    }
}