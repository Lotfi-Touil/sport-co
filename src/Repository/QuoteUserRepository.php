<?php

namespace App\Repository;

use App\Entity\QuoteUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuoteUser>
 *
 * @method QuoteUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method QuoteUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method QuoteUser[]    findAll()
 * @method QuoteUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuoteUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuoteUser::class);
    }

//    /**
//     * @return QuoteUser[] Returns an array of QuoteUser objects
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

//    public function findOneBySomeField($value): ?QuoteUser
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
