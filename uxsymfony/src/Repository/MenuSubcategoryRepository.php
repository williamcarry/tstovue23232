<?php

namespace App\Repository;

use App\Entity\MenuSubcategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MenuSubcategory>
 *
 * @method MenuSubcategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuSubcategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuSubcategory[]    findAll()
 * @method MenuSubcategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuSubcategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuSubcategory::class);
    }

    public function save(MenuSubcategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MenuSubcategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MenuSubcategory[] Returns an array of MenuSubcategory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('ms')
//            ->andWhere('ms.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('ms.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MenuSubcategory
//    {
//        return $this->createQueryBuilder('ms')
//            ->andWhere('ms.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}