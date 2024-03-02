<?php

namespace App\Service;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\CustomerRepository;
use App\Repository\PaymentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class DashboardDataService
{
    private CompanyRepository $companyRepository;
    private UserRepository $userRepository;
    private PaymentRepository $paymentRepository;
    private CustomerRepository $customerRepository;

    public function __construct(
        CompanyRepository $companyRepository,
        UserRepository $userRepository,
        PaymentRepository $paymentRepository,
        CustomerRepository $customerRepository
    ) {
        $this->companyRepository = $companyRepository;
        $this->userRepository = $userRepository;
        $this->paymentRepository = $paymentRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function prepareDataForAdmin(): array
    {
        $companyCount = $this->companyRepository->countCompanies();
        $userCount = $this->userRepository->countUsers();
        $totalAmountOfPayments = $this->paymentRepository->findTotalAmountOfPayments();
        $latestCustomers = $this->customerRepository->findLatestCustomers(5);
        $growthCompanies = $this->companyRepository->findGrowthRateCompaniesByMonth();
        $userGrowthRate = $this->userRepository->findGrowthRateUsersByMonth();
        $paymentGrowthRate = $this->paymentRepository->findGrowthRatePaymentsByMonth();
        $latestPayments = $this->paymentRepository->findLatestPayments(7);
        $paymentsByMonth = $this->paymentRepository->findTotalPaymentsByMonth();
        $signupsByMonth = $this->userRepository->findSignupCountsByMonth();


        $transactions = [];
        foreach ($latestPayments as $payment) {
            $transactions[] = [
                'description' => 'Paiement de facture de',
                'entity' => $payment->getInvoice()->getInvoiceUsers()->first()->getCustomer()->getFirstName() . ' ' . $payment->getInvoice()->getInvoiceUsers()->first()->getCustomer()->getLastName(),
                'date' => $payment->getCreatedAt()->format('M d, Y'),
                'amount' => $payment->getAmount(),
            ];
        }
        return [

            'companyCount' => $companyCount,
            'userCount' => $userCount,
            'totalAmountOfPayments' => $totalAmountOfPayments,
            'latestPayments' => $latestPayments,
            'latestCustomers' => $latestCustomers,
            'growthCompanies' => $growthCompanies,
            'userGrowthRate' => $userGrowthRate,
            'paymentGrowthRate' => $paymentGrowthRate,
            'transactions' => $transactions,
            'paymentsByMonth' => $paymentsByMonth,
            'signupsByMonth' => $signupsByMonth,
        ];
    }

    public function prepareDataForCompany(Company $company): array
    {
        $paymentsByMonth = $this->paymentRepository->findTotalPaymentsByMonthForCompany($company);
        $signupsByMonth = $this->userRepository->findSignupCountsByMonthForCompany($company);
        $customerCount = $this->customerRepository->countByCompany($company);
        $transactionsCount = $this->paymentRepository->countTransactionsByCompany($company);
        $companyRevenue = $this->paymentRepository->calculateRevenueByCompany($company);
        $latestCustomers = $this->customerRepository->findLatestCustomersForCompany($company, 5);
        $customerGrowthRate = $this->customerRepository->findGrowthRateCustomersByMonthForCompany($company);
        $transactionsGrowthRate = $this->paymentRepository->findGrowthRateTransactionsByMonthForCompany($company);
        $companyRevenueGrowthRate = $this->paymentRepository->findGrowthRatePaymentsByMonthForCompany($company);
        $latestPayments = $this->paymentRepository->findLatestPaymentsForCompany($company, 5);

        $transactions = [];
        foreach ($latestPayments as $payment) {
            $transactions[] = [
                'description' => 'Paiement de facture de', // Ajustez selon votre logique d'affichage
                'entity' => $payment->getInvoice()->getInvoiceUsers()->first()->getCustomer()->getFirstName() . ' ' . $payment->getInvoice()->getInvoiceUsers()->first()->getCustomer()->getLastName(),
                'date' => $payment->getCreatedAt()->format('M d, Y'),
                'amount' => $payment->getAmount(),
            ];
        }
        return [
            'paymentsByMonth' => $paymentsByMonth,
            'signupsByMonth' => $signupsByMonth,
            'customerCount' => $customerCount,
            'transactionsCount' => $transactionsCount,
            'companyRevenue' => $companyRevenue,
            'latestPayments' => $latestPayments,
            'latestCustomers' => $latestCustomers,
            'customerGrowthRate' => $customerGrowthRate,
            'transactionsGrowthRate' => $transactionsGrowthRate,
            'companyRevenueGrowthRate' => $companyRevenueGrowthRate,
            'transactions' => $transactions,
        ];
    }
}
