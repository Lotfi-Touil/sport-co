<?php

namespace App\Repository;

use App\Entity\BasicEmailTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BasicEmailTemplate>
 *
 * @method BasicEmailTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method BasicEmailTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method BasicEmailTemplate[]    findAll()
 * @method BasicEmailTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BasicEmailTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BasicEmailTemplate::class);
    }

//    /**
//     * @return BasicEmailTemplate[] Returns an array of BasicEmailTemplate objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BasicEmailTemplate
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
