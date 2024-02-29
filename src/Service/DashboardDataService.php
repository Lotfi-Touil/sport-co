<?php

namespace App\Service;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\CustomerRepository;
use App\Repository\PaymentRepository;
use App\Repository\UserRepository;

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

    public function prepareDataForAdmin()
    {
        $companyCount = $this->companyRepository->countCompanies();
        $userCount = $this->userRepository->countUsers();
        $totalAmountOfPayments = $this->paymentRepository->findTotalAmountOfPayments();
        $latestPayments = $this->paymentRepository->findLatestPayments(7);
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
                'description' => 'Paiement de facture de', // Ajustez selon votre logique d'affichage
                'entity' => $payment->getInvoice()->getCustomer()->getFirstName() . ' ' . $payment->getInvoice()->getCustomer()->getLastName(),
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

    public function prepareDataForCompany(Company $company)
    {
        $paymentsByMonth = $this->paymentRepository->findTotalPaymentsByMonthForCompany($company);
        $signupsByMonth = $this->userRepository->findSignupCountsByMonthForCompany($company);
        $customerCount = $this->customerRepository->countByCompany($company);
        $transactionsCount = $this->paymentRepository->countTransactionsByCompany($company);
        $companyRevenue = $this->paymentRepository->calculateRevenueByCompany($company);
        $latestPayments = $this->paymentRepository->findLatestPaymentsForCompany($company, 5);
        $latestCustomers = $this->customerRepository->findLatestCustomersForCompany($company, 5);
        $customerGrowthRate = $this->customerRepository->findGrowthRateCustomersByMonthForCompany($company);
        $transactionsGrowthRate = $this->paymentRepository->findGrowthRateTransactionsByMonthForCompany($company);
        $companyRevenueGrowthRate = $this->paymentRepository->findGrowthRatePaymentsByMonthForCompany($company);
        $latestPayments = $this->paymentRepository->findLatestPaymentsForCompany($company, 5);

        $transactions = [];
        foreach ($latestPayments as $payment) {
            $transactions[] = [
                'description' => 'Paiement de facture de', // Ajustez selon votre logique d'affichage
                'entity' => $payment->getInvoice()->getCustomer()->getFirstName() . ' ' . $payment->getInvoice()->getCustomer()->getLastName(),
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
