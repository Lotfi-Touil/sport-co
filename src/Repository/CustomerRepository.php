<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Company;

/**
 * @extends ServiceEntityRepository<Customer>
 *
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function findByTerm($term)
    {
        $searchTerms = explode(' ', $term); // Sépare le terme en mots-clés individuels
        $qb = $this->createQueryBuilder('u');

        foreach ($searchTerms as $key => $searchTerm) {
            $parameter = 'searchTerm' . $key;
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('LOWER(u.firstName)', ':' . $parameter),
                $qb->expr()->like('LOWER(u.lastName)', ':' . $parameter)
            ))->setParameter($parameter, '%' . strtolower($searchTerm) . '%');
        }

        return $qb->getQuery()->getResult();
    }

    // Function that return the last customers
    public function findLatestCustomers(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de clients appartenant à une entreprise spécifique.
     *
     * @param Company $company L'entreprise pour laquelle compter les clients.
     * @return int Le nombre de clients de l'entreprise.
     */
    public function countByCompany(Company $company): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findLatestCustomersForCompany(Company $company, int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.company = :company')
            ->setParameter('company', $company)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findGrowthRateCustomersByMonthForCompany(Company $company): float {
        $previousMonthTotal = $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.company = :company')
            ->andWhere('c.createdAt BETWEEN :startPreviousMonth AND :endPreviousMonth')
            ->setParameter('company', $company)
            ->setParameter('startPreviousMonth', (new \DateTime('first day of last month'))->format('Y-m-d'))
            ->setParameter('endPreviousMonth', (new \DateTime('last day of last month'))->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();
    
        
        $currentMonthTotal = $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.company = :company')
            ->andWhere('c.createdAt BETWEEN :startCurrentMonth AND :endCurrentMonth')
            ->setParameter('company', $company)
            ->setParameter('startCurrentMonth', (new \DateTime('first day of this month'))->format('Y-m-d'))
            ->setParameter('endCurrentMonth', (new \DateTime('now'))->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();
    
    
        if ($previousMonthTotal > 0) {
            return (($currentMonthTotal - $previousMonthTotal) / $previousMonthTotal) * 100;
        } else {
            return $currentMonthTotal > 0 ? 100 : 0;
        }
    }
    



    //    /**
    //     * @return Customer[] Returns an array of Customer objects
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

    //    public function findOneBySomeField($value): ?Customer
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
