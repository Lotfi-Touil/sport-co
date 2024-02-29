<?php

namespace App\Repository;

use App\Entity\Report;
use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Report|null find($id, $lockMode = null, $lockVersion = null)
 * @method Report|null findOneBy(array $criteria, array $orderBy = null)
 * @method Report[]    findAll()
 * @method Report[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    /**
     * Find reports by company.
     *
     * @param Company $company
     * @return Report[]
     */
    public function findByCompany(Company $company): array
    {
        return $this->findBy(['company' => $company]);
    }

    /**
     * Find the latest report for a company.
     *
     * @param Company $company
     * @return Report|null
     * @throws NonUniqueResultException
     */
    public function findLatestByCompany(Company $company): ?Report
    {
        return $this->createQueryBuilder('r')
            ->where('r.company = :company')
            ->setParameter('company', $company)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
