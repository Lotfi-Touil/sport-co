<?php
namespace App\Service;

use App\Entity\Company;
use App\Entity\Report;
use App\Repository\CompanyRepository;
use App\Repository\InvoiceProductRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PaymentRepository;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class ReportGenerationService
{
    private EntityManagerInterface $entityManager;
    private InvoiceRepository $invoiceRepository;

    private InvoiceProductRepository $invoiceProductRepository;
    private CompanyRepository $companyRepository;
    private ReportRepository $reportRepository;

    private PaymentRepository $paymentRepository;

    public function __construct(EntityManagerInterface $entityManager, InvoiceRepository $invoiceRepository, CompanyRepository $companyRepository, ReportRepository $reportRepository, InvoiceProductRepository $invoiceProductRepository, PaymentRepository $paymentRepository)
    {
        $this->entityManager = $entityManager;
        $this->invoiceRepository = $invoiceRepository;
        $this->companyRepository = $companyRepository;
        $this->reportRepository = $reportRepository;
        $this->invoiceProductRepository = $invoiceProductRepository;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function generateForCompany(?Company $company): Report
    {
        $report = new Report();
        if ($company) {
            $report->setTitle('Rapport pour ' . $company->getName() . ' du ' . date('d/m/Y'));
            $report->setCreatedAt(new \DateTime());
            $report->setCompany($company);
            $totalRevenue = $this->invoiceRepository->calculateTotalRevenueForCompany($company);
            $totalExpenses = $this->invoiceRepository->calculateTotalExpensesForCompany($company);
            $netProfit = $totalRevenue - $totalExpenses;
            $topSellingProducts = json_encode($this->invoiceProductRepository->findTopSellingProductsForCompany($company));
            $newCustomersCount = $this->invoiceRepository->countNewCustomersForCompany($company);
            $paymentDetails = json_encode($this->paymentRepository->findPaymentDetailsForCompany($company));
            $report->setTopSellingProducts($topSellingProducts);
            $report->setNewCustomersCount($newCustomersCount);
            $report->setNetProfit($netProfit);
            $report->setTotalExpenses($totalExpenses);
            $report->setTotalRevenue($totalRevenue);
            $report->setPaymentDetails($paymentDetails);
        } else {
            $report = $this->generateGlobalReport();
        }

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $report;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function generateGlobalReport(): Report {
        $report = new Report();
        $report->setTitle('Rapport global du ' . date('d/m/Y'));
        $report->setCreatedAt(new \DateTime());
        $report->setTotalRevenue($this->invoiceRepository->calculateTotalRevenue());
        $report->setTotalExpenses($this->invoiceRepository->calculateTotalExpenses());
        $report->setNetProfit($report->getTotalRevenue() - $report->getTotalExpenses());
        $topSellingProducts = json_encode($this->invoiceProductRepository->findTopSellingProducts());
        $report->setTopSellingProducts($topSellingProducts);
        $report->setNewCustomersCount($this->invoiceRepository->countNewCustomers());
        $paymentDetails = json_encode($this->paymentRepository->findPaymentDetails());
        $report->setPaymentDetails($paymentDetails);
        $this->entityManager->persist($report);
        $this->entityManager->flush();
        return $report;
    }


    public function getCompanyById(int $companyId): ?Company
    {
        return $this->companyRepository->find($companyId);
    }

    public function findAllReports(): array
    {
        return $this->reportRepository->findAll();
    }

    public function findReportsByCompany(Company $company): array
    {
        return $this->reportRepository->findBy(['company' => $company]);
    }

    public function findReportById(int $reportId): ?Report
    {
        return $this->reportRepository->find($reportId);
    }
}

