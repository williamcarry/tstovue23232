<?php

namespace App\Repository;

use App\Entity\Supplier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @extends ServiceEntityRepository<Supplier>
 *
 * @method Supplier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Supplier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Supplier[]    findAll()
 * @method Supplier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupplierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Supplier::class);
    }

    public function add(Supplier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Supplier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    public function save(Supplier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Supplier) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    /**
     * 根据审核状态查询供应商
     * 
     * @param string $auditStatus 审核状态：pending, approved, rejected, resubmit
     * @return Supplier[]
     */
    public function findByAuditStatus(string $auditStatus): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.auditStatus = :status')
            ->setParameter('status', $auditStatus)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据供应商类型查询供应商
     * 
     * @param string $supplierType 供应商类型：company, individual
     * @return Supplier[]
     */
    public function findBySupplierType(string $supplierType): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.supplierType = :type')
            ->setParameter('type', $supplierType)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取待审核的供应商列表
     * 
     * @return Supplier[]
     */
    public function findPendingSuppliers(): array
    {
        return $this->findByAuditStatus('pending');
    }

    /**
     * 获取已通过审核的供应商列表
     * 
     * @return Supplier[]
     */
    public function findApprovedSuppliers(): array
    {
        return $this->findByAuditStatus('approved');
    }

    /**
     * 获取激活的供应商（已通过审核且账号激活）
     * 
     * @return Supplier[]
     */
    public function findActiveSuppliers(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.auditStatus = :status')
            ->andWhere('s.isActive = :active')
            ->setParameter('status', 'approved')
            ->setParameter('active', true)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 分页查询供应商
     * 
     * @param array $criteria 查询条件 ['keyword' => string, 'supplierType' => string, 'auditStatus' => string, 'isActive' => bool]
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array ['data' => Supplier[], 'total' => int, 'page' => int, 'totalPages' => int]
     */
    public function findPaginated(array $criteria = [], int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('s');

        // 供应商类型筛选
        if (isset($criteria['supplierType']) && !empty($criteria['supplierType'])) {
            $qb->andWhere('s.supplierType = :supplierType')
                ->setParameter('supplierType', $criteria['supplierType']);
            error_log('Applied supplierType filter: ' . $criteria['supplierType']);
        }

        // 审核状态筛选
        if (isset($criteria['auditStatus']) && !empty($criteria['auditStatus'])) {
            $qb->andWhere('s.auditStatus = :auditStatus')
                ->setParameter('auditStatus', $criteria['auditStatus']);
            error_log('Applied auditStatus filter: ' . $criteria['auditStatus']);
        }

        // 激活状态筛选
        if (isset($criteria['isActive']) && $criteria['isActive'] !== '') {
            $qb->andWhere('s.isActive = :isActive')
                ->setParameter('isActive', $criteria['isActive']);
            error_log('Applied isActive filter: ' . $criteria['isActive']);
        }

        // 关键词搜索（用户名、邮箱、公司名称、联系人）- 完全匹配
        if (isset($criteria['keyword']) && !empty($criteria['keyword'])) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('s.username', ':keyword'),
                    $qb->expr()->eq('s.email', ':keyword'),
                    $qb->expr()->eq('s.companyName', ':keyword'),
                    $qb->expr()->eq('s.individualName', ':keyword'),
                    $qb->expr()->eq('s.contactPerson', ':keyword')
                )
            )->setParameter('keyword', $criteria['keyword']);
        }

        // 按创建时间倒序排序
        $qb->orderBy('s.createdAt', 'DESC');

        // 获取总数
        $totalQb = clone $qb;
        $total = count($totalQb->getQuery()->getResult());

        // 分页
        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        $data = $qb->getQuery()->getResult();

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit),
        ];
    }

    /**
     * 统计各审核状态的供应商数量
     * 
     * @return array ['pending' => int, 'approved' => int, 'rejected' => int, 'resubmit' => int]
     */
    public function countByAuditStatus(): array
    {
        $result = $this->createQueryBuilder('s')
            ->select('s.auditStatus, COUNT(s.id) as count')
            ->groupBy('s.auditStatus')
            ->getQuery()
            ->getResult();

        $counts = [
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'resubmit' => 0,
        ];

        foreach ($result as $row) {
            $counts[$row['auditStatus']] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * 统计各类型供应商数量
     * 
     * @return array ['company' => int, 'individual' => int]
     */
    public function countBySupplierType(): array
    {
        $result = $this->createQueryBuilder('s')
            ->select('s.supplierType, COUNT(s.id) as count')
            ->groupBy('s.supplierType')
            ->getQuery()
            ->getResult();

        $counts = [
            'company' => 0,
            'individual' => 0,
        ];

        foreach ($result as $row) {
            $counts[$row['supplierType']] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * 根据佣金比例范围查询供应商
     * 
     * @param string|null $minRate 最小佣金比例
     * @param string|null $maxRate 最大佣金比例
     * @return Supplier[]
     */
    public function findByCommissionRateRange(?string $minRate = null, ?string $maxRate = null): array
    {
        $qb = $this->createQueryBuilder('s');
        
        if ($minRate !== null) {
            $qb->andWhere('s.commissionRate >= :minRate')
                ->setParameter('minRate', $minRate);
        }
        
        if ($maxRate !== null) {
            $qb->andWhere('s.commissionRate <= :maxRate')
                ->setParameter('maxRate', $maxRate);
        }
        
        return $qb->orderBy('s.commissionRate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取有自定义佣金比例的供应商
     * 
     * @return Supplier[]
     */
    public function findWithCustomCommissionRate(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.commissionRate IS NOT NULL')
            ->orderBy('s.commissionRate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取没有自定义佣金比例的供应商
     * 
     * @return Supplier[]
     */
    public function findWithoutCustomCommissionRate(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.commissionRate IS NULL')
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}