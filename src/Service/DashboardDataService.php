<?php

namespace App\Service;

use App\Entity\Company;
use App\Repository\PaymentRepository;
use App\Repository\UserRepository;
use App\Repository\CustomerRepository;

class DashboardDataService
{
    private PaymentRepository $paymentRepository;
    private UserRepository $userRepository;
    private CustomerRepository $customerRepository;

    public function __construct(PaymentRepository $paymentRepository, UserRepository $userRepository, CustomerRepository $customerRepository)
    {
        $this->paymentRepository = $paymentRepository;
        $this->userRepository = $userRepository;
        $this->customerRepository = $customerRepository;
    }

    public function prepareDataForAdmin()
    {
        $paymentsByMonth = $this->paymentRepository->findTotalPaymentsByMonth();
        $signupsByMonth = $this->userRepository->findSignupCountsByMonth();
        $customerCount = $this->customerRepository->countAllCustomers();
        $transactionsCount = $this->paymentRepository->countAllTransactions();
        $latestPayments = $this->paymentRepository->findLatestPayments(5);
        $latestCustomers = $this->customerRepository->findLatestCustomers(5);

        return [
            'paymentsByMonth' => $paymentsByMonth,
            'signupsByMonth' => $signupsByMonth,
            'customerCount' => $customerCount,
            'transactionsCount' => $transactionsCount,
            'latestPayments' => $latestPayments,
            'latestCustomers' => $latestCustomers,
        ];
    }

    public function prepareDataForCompany(Company $company)
    {
        $paymentsByMonth = $this->paymentRepository->findTotalPaymentsByMonthForCompany($company);
        $signupsByMonth = $this->userRepository->findSignupCountsByMonthForCompany($company);
        $customerCount = $this->customerRepository->countByCompany($company);
        $transactionsCount = $this->paymentRepository->countTransactionsByCompany($company);
        $latestPayments = $this->paymentRepository->findLatestPaymentsForCompany($company, 5);
        $latestCustomers = $this->customerRepository->findLatestCustomersForCompany($company, 5);

        return [
            'paymentsByMonth' => $paymentsByMonth,
            'signupsByMonth' => $signupsByMonth,
            'customerCount' => $customerCount,
            'transactionsCount' => $transactionsCount,
            'latestPayments' => $latestPayments,
            'latestCustomers' => $latestCustomers,
        ];
    }
}
