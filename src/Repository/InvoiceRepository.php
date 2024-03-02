<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 *
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * Calcule le revenu total pour une entreprise spécifique.
     *
     * @param Company $company L'entreprise pour laquelle calculer le revenu total.
     * @return float Le revenu total pour l'entreprise donnée.
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function calculateTotalRevenueForCompany(Company $company): float
    {
        $qb = $this->createQueryBuilder('i')
            ->select('SUM(i.totalAmount) as totalRevenue')
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->where('c.company = :company')
            ->setParameter('company', $company)
            ->getQuery();

        return (float) $qb->getSingleScalarResult();
    }

    /**
     * Calcule le revenu total pour toutes les entreprises.
     *
     * @return float Le revenu total.
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function calculateTotalRevenue(): float
    {
        $qb = $this->createQueryBuilder('i')
            ->select('SUM(i.totalAmount) as totalRevenue')
            ->getQuery();

        return (float) $qb->getSingleScalarResult();
    }

    /**
     * Calcule le total des dépenses pour une entreprise spécifique.
     *
     * @param Company $company L'entreprise pour laquelle calculer les dépenses.
     * @return float Le total des dépenses pour l'entreprise donnée.
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function calculateTotalExpensesForCompany(Company $company): float
    {
        $qb = $this->createQueryBuilder('i')
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->where('c.company = :company')
            ->setParameter('company', $company)
            ->select('SUM(i.totalAmount) as totalExpenses')
            ->getQuery();

        return (float) $qb->getSingleScalarResult();
    }

    /**
     * Calcule le total des dépenses pour toutes les entreprises.
     *
     * @return float Le total des dépenses.
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function calculateTotalExpenses(): float
    {
        $qb = $this->createQueryBuilder('i')
        ->select('SUM(i.totalAmount) as totalExpenses')
        ->getQuery();

        return (float) $qb->getSingleScalarResult();
    }

//    /**
//     * @return Invoice[] Returns an array of Invoice objects
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

//    public function findOneBySomeField($value): ?Invoice
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
