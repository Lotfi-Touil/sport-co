<?php

namespace App\Repository;

use App\Entity\InvoiceUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceUser>
 *
 * @method InvoiceUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceUser[]    findAll()
 * @method InvoiceUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceUser::class);
    }

    public function findByInvoiceId($invoiceId): ?InvoiceUser
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.invoice = :val')
            ->setParameter('val', $invoiceId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return InvoiceUser[] Returns an array of InvoiceUser objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?InvoiceUser
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
