<?php

namespace App\Repository;

use App\Entity\PageAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageAccess>
 *
 * @method PageAccess|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageAccess|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageAccess[]    findAll()
 * @method PageAccess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageAccess::class);
    }

//    /**
//     * @return PageAccess[] Returns an array of PageAccess objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PageAccess
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
