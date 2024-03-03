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

    public function findAllByCompanyId($companyId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.company = :val')
            ->setParameter('val', $companyId)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByTermAndCompany($term, $company)
    {
        $terms = explode(' ', $term); // Sépare le terme en mots-clés individuels
        $qb = $this->createQueryBuilder('c');

        $qb->andWhere('c.company = :company')
        ->setParameter('company', $company);

        foreach ($terms as $key => $term) {
            $parameter = 'searchTerm' . $key;
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('LOWER(c.firstName)', ':' . $parameter),
                $qb->expr()->like('LOWER(c.lastName)', ':' . $parameter)
            ))->setParameter($parameter, '%' . strtolower($term) . '%');
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
            ->setParameter('endCurrentMonth', (new \DateTime('last day of this month'))->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();

        if ($previousMonthTotal > 0) {
            return (($currentMonthTotal - $previousMonthTotal) / $previousMonthTotal) * 100;
        } else {
            return $currentMonthTotal > 0 ? 100 : 0;
        }
    }

    /**
     * Compte le nombre de nouveaux clients pour une entreprise spécifique.
     */
    public function countNewCustomersForCompany(Company $company): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id)')
            ->where('c.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre total de nouveaux clients.
     */
    public function countNewCustomers(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
