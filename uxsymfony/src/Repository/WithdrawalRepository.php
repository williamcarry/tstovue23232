<?php

namespace App\Repository;

use App\Entity\Withdrawal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Withdrawal>
 *
 * @method Withdrawal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Withdrawal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Withdrawal[]    findAll()
 * @method Withdrawal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WithdrawalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Withdrawal::class);
    }

    public function add(Withdrawal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Withdrawal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 根据状态获取提现申请列表
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.status = :status')
            ->setParameter('status', $status)
            ->orderBy('w.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据供应商ID获取提现申请列表
     */
    public function findBySupplierId(int $supplierId): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.supplierId = :supplierId')
            ->setParameter('supplierId', $supplierId)
            ->orderBy('w.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取带分页的提现申请列表
     */
    public function findWithPagination(int $page, int $limit, array $criteria = []): array
    {
        $qb = $this->createQueryBuilder('w');

        // 添加筛选条件
        if (isset($criteria['status']) && $criteria['status'] !== '') {
            $qb->andWhere('w.status = :status')
                ->setParameter('status', $criteria['status']);
        }

        if (isset($criteria['supplierIds']) && !empty($criteria['supplierIds'])) {
            $qb->andWhere('w.supplierId IN (:supplierIds)')
                ->setParameter('supplierIds', $criteria['supplierIds']);
        } elseif (isset($criteria['supplierId']) && $criteria['supplierId'] !== '') {
            $qb->andWhere('w.supplierId = :supplierId')
                ->setParameter('supplierId', $criteria['supplierId']);
        }

        // 添加审核时间范围条件
        if (isset($criteria['reviewedAtStart']) && $criteria['reviewedAtStart'] !== '' && 
            isset($criteria['reviewedAtEnd']) && $criteria['reviewedAtEnd'] !== '') {
            // 日期范围查询
            $qb->andWhere('w.reviewedAt >= :reviewedAtStart AND w.reviewedAt <= :reviewedAtEnd')
                ->setParameter('reviewedAtStart', $criteria['reviewedAtStart'])
                ->setParameter('reviewedAtEnd', $criteria['reviewedAtEnd']);
        } elseif (isset($criteria['reviewedAtStart']) && $criteria['reviewedAtStart'] !== '') {
            // 只有开始时间
            $qb->andWhere('w.reviewedAt >= :reviewedAtStart')
                ->setParameter('reviewedAtStart', $criteria['reviewedAtStart']);
        } elseif (isset($criteria['reviewedAtEnd']) && $criteria['reviewedAtEnd'] !== '') {
            // 只有结束时间
            $qb->andWhere('w.reviewedAt <= :reviewedAtEnd')
                ->setParameter('reviewedAtEnd', $criteria['reviewedAtEnd']);
        }

        // 获取总数
        $totalItems = (int) $qb->select('COUNT(w.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        // 获取分页数据
        $qb->select('w')
            ->orderBy('w.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return [
            'items' => $items,
            'totalItems' => $totalItems,
            'totalPages' => ceil($totalItems / $limit),
        ];
    }
}