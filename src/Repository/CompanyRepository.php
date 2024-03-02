<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 *
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    // Function that return the number of companies in the database
    public function countCompanies(): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Function that return the growth of companies in the database by month that returns an integer for the percentage of growth

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function findGrowthRateCompaniesByMonth(): int
    {
        $qb = $this->createQueryBuilder('c');
        $previousMonthCount = $qb->select('count(c.id)')
            ->where('c.createdAt BETWEEN :startPreviousMonth AND :endPreviousMonth')
            ->setParameter('startPreviousMonth', (new \DateTime('first day of last month'))->format('Y-m-d'))
            ->setParameter('endPreviousMonth', (new \DateTime('last day of last month'))->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();

        $qb = $this->createQueryBuilder('c');
        $currentMonthCount = $qb->select('count(c.id)')
            ->where('c.createdAt BETWEEN :startCurrentMonth AND :endCurrentMonth')
            ->setParameter('startCurrentMonth', (new \DateTime('first day of this month'))->format('Y-m-d'))
            ->setParameter('endCurrentMonth', (new \DateTime('last day of this month'))->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();

        if ($previousMonthCount > 0) {
            $growthRate = (($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100;
        } else {
            $growthRate = $currentMonthCount > 0 ? 100 : 0;
        }

        return (int) round($growthRate);
    }





    // Function that reuturn the list of companies recently created
    public function findLatestCompanies(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Company[] Returns an array of Company objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Company
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
