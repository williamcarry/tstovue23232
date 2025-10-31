<?php

namespace App\Repository;

use App\Entity\LogisticsCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LogisticsCompany>
 *
 * @method LogisticsCompany|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogisticsCompany|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogisticsCompany[]    findAll()
 * @method LogisticsCompany[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogisticsCompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogisticsCompany::class);
    }

    public function add(LogisticsCompany $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LogisticsCompany $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 获取所有启用的物流公司，按排序字段升序排列
     *
     * @return LogisticsCompany[]
     */
    public function findAllActiveOrderBySort(): array
    {
        return $this->createQueryBuilder('lc')
            ->where('lc.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('lc.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据名称查找启用的物流公司
     *
     * @param string $name
     * @return LogisticsCompany|null
     */
    public function findOneActiveByName(string $name): ?LogisticsCompany
    {
        return $this->createQueryBuilder('lc')
            ->where('lc.name = :name')
            ->andWhere('lc.isActive = :isActive')
            ->setParameter('name', $name)
            ->setParameter('isActive', true)
            ->getQuery()
            ->getOneOrNullResult();
    }
}