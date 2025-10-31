<?php

namespace App\Repository;

use App\Entity\PromotionMenu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PromotionMenu>
 *
 * @method PromotionMenu|null find($id, $lockMode = null, $lockVersion = null)
 * @method PromotionMenu|null findOneBy(array $criteria, array $orderBy = null)
 * @method PromotionMenu[]    findAll()
 * @method PromotionMenu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromotionMenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromotionMenu::class);
    }

    public function save(PromotionMenu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PromotionMenu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PromotionMenu[] Returns an array of PromotionMenu objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('pm')
//            ->andWhere('pm.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('pm.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PromotionMenu
//    {
//        return $this->createQueryBuilder('pm')
//            ->andWhere('pm.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}