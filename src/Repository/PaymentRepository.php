<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Company;

/**
 * @extends ServiceEntityRepository<Payment>
 *
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function findAllByCompanyId($companyId): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.invoice', 'i')
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->where('c.company = :val')
            ->setParameter('val', $companyId)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByInvoiceId($invoiceId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.invoice = :val')
            ->setParameter('val', $invoiceId)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findTotalPaymentsByMonth()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
        SELECT
            EXTRACT(YEAR FROM created_at) AS year,
            TO_CHAR(created_at, 'Month') AS month,
            SUM(amount) AS total
        FROM
            payment
        GROUP BY
            year, month
        ORDER BY
            year ASC, month ASC;
    ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    public function findTotalPaymentsByMonthForCompany(Company $company)
    {
        $result = $this->createQueryBuilder('p')
            ->select("p.createdAt, SUM(p.amount) AS total")
            ->join('p.invoice', 'i')
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->where('c.company = :company')
            ->setParameter('company', $company)
            ->groupBy('p.createdAt')
            ->orderBy('p.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        $monthlyTotals = [];
        foreach ($result as $payment) {
            $year = $payment['createdAt']->format('Y');
            $month = $payment['createdAt']->format('F');
            if (!isset($monthlyTotals[$year][$month])) {
                $monthlyTotals[$year][$month] = 0;
            }
            $monthlyTotals[$year][$month] += $payment['total'];
        }

        
        $formattedResults = [];
        foreach ($monthlyTotals as $year => $months) {
            foreach ($months as $month => $total) {
                $formattedResults[] = [
                    'year' => $year,
                    'month' => $month,
                    'total' => $total,
                ];
            }
        }

        return $formattedResults;
    }

    // function that returns the total amount of payments
    public function findTotalAmountOfPayments(): ?int
    {
        return $this->createQueryBuilder('p')
            ->select('SUM(p.amount)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findLatestPaymentsForCompany(Company $company, int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.invoice', 'i') 
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->where('c.company = :company') 
            ->setParameter('company', $company)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // Function that return the last 5 payments
    public function findLatestPayments(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // Function taht returns the total amount of payments by week
    public function findTotalPaymentsByWeek(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
        SELECT
            EXTRACT(YEAR FROM created_at) AS year,
            EXTRACT(WEEK FROM created_at) AS week,
            SUM(amount) AS total
        FROM
            payment
        GROUP BY
            year, week
        ORDER BY
            year ASC, week ASC;
    ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    public function findGrowthRatePaymentsByMonth(): float
    {
        $qb = $this->createQueryBuilder('p');

        
        $previousMonthTotal = $qb->select('SUM(p.amount)')
            ->where('p.createdAt BETWEEN :startPreviousMonth AND :endPreviousMonth')
            ->setParameter('startPreviousMonth', (new \DateTime('first day of last month'))->format('Y-m-d'))
            ->setParameter('endPreviousMonth', (new \DateTime('last day of last month'))->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();

        $qb = $this->createQueryBuilder('p');

        
        $currentMonthTotal = $qb->select('SUM(p.amount)')
            ->where('p.createdAt BETWEEN :startCurrentMonth AND :endCurrentMonth')
            ->setParameter('startCurrentMonth', (new \DateTime('first day of this month'))->format('Y-m-d'))
            ->setParameter('endCurrentMonth', (new \DateTime('last day of this month'))->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();

        
        if ($previousMonthTotal > 0) {
            $growthRate = (($currentMonthTotal - $previousMonthTotal) / $previousMonthTotal) * 100;
        } else {
            $growthRate = $currentMonthTotal > 0 ? 100 : 0; 
        }

        return round($growthRate, 2);
    }

    public function findGrowthRatePaymentsByMonthForCompany(Company $company): float
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.invoice', 'i')
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->where('c.company = :company')
            ->setParameter('company', $company);

        
        $previousMonthTotal = $qb
            ->select('SUM(p.amount)')
            ->andWhere('p.createdAt BETWEEN :startPreviousMonth AND :endPreviousMonth')
            ->setParameter('startPreviousMonth', (new \DateTime('first day of last month'))->format('Y-m-d'))
            ->setParameter('endPreviousMonth', (new \DateTime('last day of last month'))->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();

        
        $qb = $this->createQueryBuilder('p')
            ->join('p.invoice', 'i')
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->where('c.company = :company')
            ->setParameter('company', $company);

    
        $currentMonthTotal = $qb
            ->select('SUM(p.amount)')
            ->andWhere('p.createdAt BETWEEN :startCurrentMonth AND :endCurrentMonth')
            ->setParameter('startCurrentMonth', (new \DateTime('first day of this month'))->format('Y-m-d'))
            ->setParameter('endCurrentMonth', (new \DateTime('last day of this month'))->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();

    
        if ($previousMonthTotal > 0) {
            $growthRate = (($currentMonthTotal - $previousMonthTotal) / $previousMonthTotal) * 100;
        } else {
            $growthRate = $currentMonthTotal > 0 ? 100 : 0;
        }

        return round($growthRate, 2);
    }

    public function findGrowthRateTransactionsByMonthForCompany(Company $company): float {
        $qb = $this->createQueryBuilder('p');
        
        
        $previousMonthTotal = $qb
            ->select('COUNT(p.id)')
            ->join('p.invoice', 'i') 
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->where('c.company = :company') 
            ->andWhere('p.createdAt BETWEEN :startPreviousMonth AND :endPreviousMonth')
            ->setParameter('company', $company)
            ->setParameter('startPreviousMonth', (new \DateTime('first day of last month'))->format('Y-m-d'))
            ->setParameter('endPreviousMonth', (new \DateTime('last day of last month'))->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();

        $qb = $this->createQueryBuilder('p');
        
        
        $currentMonthTotal = $qb
            ->select('COUNT(p.id)')
            ->join('p.invoice', 'i')
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->where('c.company = :company')
            ->andWhere('p.createdAt BETWEEN :startCurrentMonth AND :endCurrentMonth')
            ->setParameter('company', $company)
            ->setParameter('startCurrentMonth', (new \DateTime('first day of this month'))->format('Y-m-d'))
            ->setParameter('endCurrentMonth', (new \DateTime('last day of this month'))->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();
        
        
        if ($previousMonthTotal > 0) {
            $growthRate = (($currentMonthTotal - $previousMonthTotal) / $previousMonthTotal) * 100;
        } else {
            $growthRate = $currentMonthTotal > 0 ? 100 : 0; 
        }
        
        return round($growthRate, 2); 
    }

    public function findGrowthRateRevenueByWeek(): float
    {
        $qb = $this->createQueryBuilder('p');

        
        $startPreviousWeek = (new \DateTime())->modify('last week monday')->format('Y-m-d');
        $endPreviousWeek = (new \DateTime())->modify('last sunday')->format('Y-m-d');

        
        $previousWeekTotal = $qb->select('SUM(p.amount)')
            ->where('p.createdAt BETWEEN :startPreviousWeek AND :endPreviousWeek')
            ->setParameter('startPreviousWeek', $startPreviousWeek)
            ->setParameter('endPreviousWeek', $endPreviousWeek)
            ->getQuery()
            ->getSingleScalarResult();

        $qb = $this->createQueryBuilder('p');

        
        $startCurrentWeek = (new \DateTime())->modify('this week monday')->format('Y-m-d');
        $endCurrentWeek = (new \DateTime())->format('Y-m-d'); 
        
        $currentWeekTotal = $qb->select('SUM(p.amount)')
            ->where('p.createdAt BETWEEN :startCurrentWeek AND :endCurrentWeek')
            ->setParameter('startCurrentWeek', $startCurrentWeek)
            ->setParameter('endCurrentWeek', $endCurrentWeek)
            ->getQuery()
            ->getSingleScalarResult();

        
        if ($previousWeekTotal > 0) {
            $growthRate = (($currentWeekTotal - $previousWeekTotal) / $previousWeekTotal) * 100;
        } else {
            $growthRate = $currentWeekTotal > 0 ? 100 : 0; 
        }

        return round($growthRate, 2); 
    }

    /**
     * Compte le nombre de transactions pour une entreprise spécifique.
     *
     * @param Company $company L'entité de l'entreprise pour laquelle compter les transactions.
     * @return int Le nombre de transactions associées à l'entreprise.
     */
    public function countTransactionsByCompany(Company $company): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->join('p.invoice', 'i')
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->where('c.company = :company')
            ->setParameter('company', $company);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    public function calculateRevenueByCompany($company)
    {
        return $this->createQueryBuilder('p')
            ->select('SUM(p.amount) as totalRevenue')
            ->join('p.invoice', 'i')
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->where('c.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getSingleScalarResult();
    }


    /**
     * Récupère les détails de tous les paiements et les retourne sous forme de chaîne JSON.
     *
     * @return string Les détails de tous les paiements en format JSON.
     */
    public function findPaymentDetails(): array
    {
        return $this->createQueryBuilder('p')
            ->select('
                p.id, 
                p.amount, 
                p.createdAt, 
                i.id AS invoiceId, 
                i.totalAmount AS invoiceTotal,
                c.firstName AS customerFirstName,
                c.lastName AS customerLastName
            ')
            ->join('p.invoice', 'i')
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Récupère les détails des paiements pour une entreprise spécifique et les retourne sous forme de chaîne JSON.
     *
     * @param Company $company L'entreprise concernée.
     * @return string Les détails des paiements pour l'entreprise donnée en format JSON.
     */
    public function findPaymentDetailsForCompany(Company $company): array
    {
        return $this->createQueryBuilder('p')
            ->select('
                p.id, 
                p.amount, 
                p.createdAt, 
                i.id AS invoiceId, 
                i.totalAmount AS invoiceTotal,
                c.firstName AS customerFirstName,
                c.lastName AS customerLastName
            ')
            ->join('p.invoice', 'i')
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->where('c.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getArrayResult();
    }

    public function findOverduePayments(PaymentStatusRepository $paymentStatusRepository)
    {
        $status = $paymentStatusRepository->findOneBy(['name' => 'En attente']);

        if (!$status) {
            return [];
        }

        $qb = $this->createQueryBuilder('p');
        $qb->where('p.paymentStatus = :status')
            ->andWhere('p.createdAt <= :threshold')
            ->setParameter('status', $status)
            ->setParameter('threshold', new \DateTime('-7 days'));

        return $qb->getQuery()->getResult();
    }


}
