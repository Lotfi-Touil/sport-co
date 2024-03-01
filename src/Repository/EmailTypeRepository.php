<?php

namespace App\Repository;

use App\Entity\EmailType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailType>
 *
 * @method EmailType|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailType|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailType[]    findAll()
 * @method EmailType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailType::class);
    }

//    /**
//     * @return EmailType[] Returns an array of EmailType objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EmailType
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
