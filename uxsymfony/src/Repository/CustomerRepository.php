<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function add(Customer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Customer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 分页查询会员（支持多条件筛选）
     * 
     * @param array $criteria 查询条件
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array ['data' => Customer[], 'total' => int, 'page' => int, 'totalPages' => int, 'limit' => int]
     */
    public function findPaginated(array $criteria = [], int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('c');

        // 关键词搜索（用户名、邮箱、昵称、真实姓名等）- 完全匹配
        if (isset($criteria['keyword']) && !empty($criteria['keyword'])) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('c.username', ':keyword'),
                    $qb->expr()->eq('c.email', ':keyword'),
                    $qb->expr()->eq('c.nickname', ':keyword'),
                    $qb->expr()->eq('c.realName', ':keyword'),
                    $qb->expr()->eq('c.mobile', ':keyword')
                )
            )->setParameter('keyword', $criteria['keyword']);
        }

        // 按创建时间倒序排序
        $qb->orderBy('c.createdAt', 'DESC');

        // 获取总数（用于计算总页数）
        $totalQb = clone $qb;
        $total = count($totalQb->getQuery()->getResult());

        // 计算偏移量并设置分页
        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        $data = $qb->getQuery()->getResult();

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit),  // 向上取整计算总页数
        ];
    }

    // 可以在这里添加自定义的查询方法
}