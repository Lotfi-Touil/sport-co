<?php

namespace App\Repository;

use App\Entity\QuoteInvoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuoteInvoice>
 *
 * @method QuoteInvoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method QuoteInvoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method QuoteInvoice[]    findAll()
 * @method QuoteInvoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuoteInvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuoteInvoice::class);
    }

//    /**
//     * @return QuoteInvoice[] Returns an array of QuoteInvoice objects
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

//    public function findOneBySomeField($value): ?QuoteInvoice
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
