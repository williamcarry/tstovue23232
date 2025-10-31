<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Supplier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function add(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 分页查询商品（供应商专用）
     * 
     * @param Supplier $supplier 供应商
     * @param array $criteria 查询条件
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array ['data' => Product[], 'total' => int, 'page' => int, 'totalPages' => int, 'limit' => int]
     */
    public function findPaginatedBySupplier(Supplier $supplier, array $criteria = [], int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('p');

        // 必须是当前供应商的商品
        $qb->andWhere('p.supplier = :supplier')
            ->setParameter('supplier', $supplier);

        // 关键词搜索（SKU、SPU、商品标题）
        if (isset($criteria['keyword']) && !empty($criteria['keyword'])) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('p.sku', ':keyword'),
                    $qb->expr()->like('p.spu', ':keyword'),
                    $qb->expr()->like('p.title', ':keyword'),
                    $qb->expr()->like('p.titleEn', ':keyword')
                )
            )->setParameter('keyword', '%' . $criteria['keyword'] . '%');
        }

        // 商品状态筛选
        if (isset($criteria['status']) && !empty($criteria['status'])) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $criteria['status']);
        }

        // 一级分类筛选
        if (isset($criteria['categoryId']) && !empty($criteria['categoryId'])) {
            $qb->andWhere('p.category = :categoryId')
                ->setParameter('categoryId', $criteria['categoryId']);
        }

        // 二级分类筛选
        if (isset($criteria['subcategoryId']) && !empty($criteria['subcategoryId'])) {
            $qb->andWhere('p.subcategory = :subcategoryId')
                ->setParameter('subcategoryId', $criteria['subcategoryId']);
        }

        // 三级分类筛选
        if (isset($criteria['itemId']) && !empty($criteria['itemId'])) {
            $qb->andWhere('p.item = :itemId')
                ->setParameter('itemId', $criteria['itemId']);
        }

        // 是否新品筛选
        if (isset($criteria['isNew']) && $criteria['isNew'] !== '') {
            $qb->andWhere('p.isNew = :isNew')
                ->setParameter('isNew', $criteria['isNew']);
        }

        // 是否热卖筛选
        if (isset($criteria['isHot']) && $criteria['isHot'] !== '') {
            $qb->andWhere('p.isHot = :isHot')
                ->setParameter('isHot', $criteria['isHot']);
        }

        // 是否促销筛选
        if (isset($criteria['isPromotion']) && $criteria['isPromotion'] !== '') {
            $qb->andWhere('p.isPromotion = :isPromotion')
                ->setParameter('isPromotion', $criteria['isPromotion']);
        }

        // 排序
        $sortField = $criteria['sortField'] ?? 'createdAt';
        $sortOrder = $criteria['sortOrder'] ?? 'DESC';
        $qb->orderBy('p.' . $sortField, $sortOrder);

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
     * 根据SKU查找商品
     */
    public function findOneBySku(string $sku): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.sku = :sku')
            ->setParameter('sku', $sku)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 根据状态获取商品数量
     */
    public function countByStatus(Supplier $supplier, string $status): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.supplier = :supplier')
            ->andWhere('p.status = :status')
            ->setParameter('supplier', $supplier)
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * 获取供应商的商品统计信息
     */
    public function getSupplierStatistics(Supplier $supplier): array
    {
        return [
            'total' => $this->count(['supplier' => $supplier]),
            'draft' => $this->countByStatus($supplier, 'draft'),
            'pending' => $this->countByStatus($supplier, 'pending'),
            'approved' => $this->countByStatus($supplier, 'approved'),
            'rejected' => $this->countByStatus($supplier, 'rejected'),
            'offline' => $this->countByStatus($supplier, 'offline'),
        ];
    }

    /**
     * 分页查询商品（管理员专用）
     * 
     * @param array $criteria 查询条件
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array ['data' => Product[], 'total' => int, 'page' => int, 'totalPages' => int, 'limit' => int]
     */
    public function findPaginatedForAdmin(array $criteria = [], int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.supplier', 's')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.subcategory', 'sc')
            ->leftJoin('p.item', 'i')
            ->addSelect('s', 'c', 'sc', 'i');

        // 关键词搜索（SKU、SPU、商品标题、供应商名称）
        if (isset($criteria['keyword']) && !empty($criteria['keyword'])) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('p.sku', ':keyword'),
                    $qb->expr()->like('p.spu', ':keyword'),
                    $qb->expr()->like('p.title', ':keyword'),
                    $qb->expr()->like('p.titleEn', ':keyword'),
                    $qb->expr()->like('s.username', ':keyword'),
                    $qb->expr()->like('s.companyName', ':keyword')
                )
            )->setParameter('keyword', '%' . $criteria['keyword'] . '%');
        }

        // 商品状态筛选
        if (isset($criteria['status']) && !empty($criteria['status'])) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $criteria['status']);
        }

        // 供应商筛选 - 支持通过用户名精确匹配查找
        if (isset($criteria['supplierUsername']) && !empty($criteria['supplierUsername'])) {
            // 按供应商用户名精确匹配筛选
            $qb->andWhere('s.username = :supplierUsername')
                ->setParameter('supplierUsername', $criteria['supplierUsername']);
        }

        // 一级分类筛选
        if (isset($criteria['categoryId']) && !empty($criteria['categoryId'])) {
            $qb->andWhere('p.category = :categoryId')
                ->setParameter('categoryId', $criteria['categoryId']);
        }

        // 二级分类筛选
        if (isset($criteria['subcategoryId']) && !empty($criteria['subcategoryId'])) {
            $qb->andWhere('p.subcategory = :subcategoryId')
                ->setParameter('subcategoryId', $criteria['subcategoryId']);
        }

        // 三级分类筛选
        if (isset($criteria['itemId']) && !empty($criteria['itemId'])) {
            $qb->andWhere('p.item = :itemId')
                ->setParameter('itemId', $criteria['itemId']);
        }

        // 排序
        $sortField = $criteria['sortField'] ?? 'createdAt';
        $sortOrder = $criteria['sortOrder'] ?? 'DESC';
        $qb->orderBy('p.' . $sortField, $sortOrder);

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
     * 获取管理员的商品统计信息
     */
    public function getAdminStatistics(): array
    {
        return [
            'total' => $this->count([]),
            'draft' => $this->count(['status' => 'draft']),
            'pending' => $this->count(['status' => 'pending']),
            'approved' => $this->count(['status' => 'approved']),
            'rejected' => $this->count(['status' => 'rejected']),
            'offline' => $this->count(['status' => 'offline']),
        ];
    }
}