<?php

namespace App\Repository;

use App\Entity\QuoteStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuoteStatus>
 *
 * @method QuoteStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method QuoteStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method QuoteStatus[]    findAll()
 * @method QuoteStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuoteStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuoteStatus::class);
    }

//    /**
//     * @return QuoteStatus[] Returns an array of QuoteStatus objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('q.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?QuoteStatus
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
