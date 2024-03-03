<?php

namespace App\Repository;

use App\Entity\InvoiceStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceStatus>
 *
 * @method InvoiceStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceStatus[]    findAll()
 * @method InvoiceStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceStatus::class);
    }

    public function findAllByCompanyId($companyId, $includeNullCompany = false): array
    {
        $qb = $this->createQueryBuilder('i');

        if ($includeNullCompany) {
            $qb->where('i.company = :val OR i.company IS NULL');
        } else {
            $qb->where('i.company = :val');
        }

        $qb->setParameter('val', $companyId)
           ->orderBy('i.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return InvoiceStatus[] Returns an array of InvoiceStatus objects
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

//    public function findOneBySomeField($value): ?InvoiceStatus
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
