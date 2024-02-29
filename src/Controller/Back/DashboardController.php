<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\PaymentRepository;
use App\Repository\UserRepository;
use App\Repository\CompanyRepository;
use App\Repository\CustomerRepository;

class DashboardController extends AbstractController
{
    private $companyRepository;
    private $userRepository;
    private $paymentRepository;
    private $customerRepository;

    public function __construct(CompanyRepository $companyRepository, UserRepository $userRepository, PaymentRepository $paymentRepository, CustomerRepository $customerRepository)
    {
        $this->companyRepository = $companyRepository;
        $this->userRepository = $userRepository;
        $this->paymentRepository = $paymentRepository;
        $this->customerRepository = $customerRepository;
    }


    #[Route('/platform/dashboard', name: 'platform_dashboard')]
    public function index(AuthorizationCheckerInterface $authorizationChecker, ChartBuilderInterface $chartBuilder, PaymentRepository $paymentRepository, UserRepository $userRepository): Response
    {
        if ($authorizationChecker->isGranted('ROLE_ADMIN')) {
            return $this->adminDashboard($chartBuilder, $paymentRepository, $userRepository);
        } elseif ($authorizationChecker->isGranted('ROLE_COMPANY') || $authorizationChecker->isGranted('ROLE_USER')) {
            return $this->companyDashboard($chartBuilder, $paymentRepository, $userRepository);
        }

        return $this->render('some_default_or_error_template.html.twig');
    }


    private function adminDashboard(ChartBuilderInterface $chartBuilder, PaymentRepository $paymentRepository, UserRepository $userRepository): Response
    {
        $paymentsByMonth = $paymentRepository->findTotalPaymentsByMonth();
        $signupsByMonth = $userRepository->findSignupCountsByMonth();

        $paymentLabels = array_column($paymentsByMonth, 'month');
        $paymentData = array_column($paymentsByMonth, 'total');

        $paymentChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $paymentChart->setData([
            'labels' => $paymentLabels,
            'datasets' => [
                [
                    'label' => 'Total de paiements',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'data' => $paymentData,
                ],
            ],
        ]);




        $signupLabels = array_column($signupsByMonth, 'month');
        $signupData = array_column($signupsByMonth, 'count');


        $signupChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $signupChart->setData([
            'labels' => $signupLabels,
            'datasets' => [
                [
                    'label' => 'Nouveaux utilisateurs inscrits',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'data' => $signupData,
                ],
            ],
        ]);

        $companyCount = 0;
        $userCount = 0;
        $totalAmountOfPayments = 0;
        $totalAmountOfPaymentsByWeek = 0;
        $latestPayments = [];
        $latestCustomers = [];
        $growthCompanies = 0;
        $userGrowthRate = 0;
        $paymentGrowthRate = 0;
        $companyCount = $this->companyRepository->countCompanies();
        $userCount = $this->userRepository->countUsers();
        $totalAmountOfPayments = $this->paymentRepository->findTotalAmountOfPayments();
        $latestPayments = $this->paymentRepository->findLatestPayments(7);
        $totalAmountOfPaymentsByWeek = $this->paymentRepository->findTotalPaymentsByWeek();
        $latestCustomers = $this->customerRepository->findLatestCustomers(5);
        $growthCompanies = $this->companyRepository->findGrowthRateCompaniesByMonth();
        $userGrowthRate = $this->userRepository->findGrowthRateUsersByMonth();
        $paymentGrowthRate = $this->paymentRepository->findGrowthRatePaymentsByMonth();

        $transactions = [];
        foreach ($latestPayments as $payment) {
            $transactions[] = [
                'description' => 'Paiement de facture de',
                'entity' => $payment->getInvoice()->getCustomer()->getFirstName() . ' ' . $payment->getInvoice()->getCustomer()->getLastName(),
                'date' => $payment->getCreatedAt()->format('M d, Y'),
                'amount' => $payment->getAmount(),
            ];
        }
        return $this->render('back/dashboard/admin_dashboard.html.twig', [
            'paymentChart' => $paymentChart,
            'signupChart' => $signupChart,
            'companyCount' => $companyCount,
            'userCount' => $userCount,
            'totalAmountOfPayments' => $totalAmountOfPayments,
            'transactions' => $transactions,
            'totalAmountOfPaymentsByWeek' => $totalAmountOfPaymentsByWeek,
            'latestCustomers' => $latestCustomers,
            'growthCompanies' => $growthCompanies,
            'userGrowthRate' => $userGrowthRate,
            'paymentGrowthRate' => $paymentGrowthRate,
        ]);
    }


    private function companyDashboard(ChartBuilderInterface $chartBuilder, PaymentRepository $paymentRepository, UserRepository $userRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw new \LogicException('No user found');
        }

        $company = $user->getCompany();

        $paymentsByMonth = $paymentRepository->findTotalPaymentsByMonthForCompany($company);
        $signupsByMonth = $userRepository->findSignupCountsByMonthForCompany($company);

        $paymentLabels = array_column($paymentsByMonth, 'month');
        $paymentData = array_column($paymentsByMonth, 'total');

        $paymentChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $paymentChart->setData([
            'labels' => $paymentLabels,
            'datasets' => [
                [
                    'label' => 'Total de paiements',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'data' => $paymentData,
                ],
            ],
        ]);

        $signupLabels = array_column($signupsByMonth, 'month');
        $signupData = array_column($signupsByMonth, 'count');


        $signupChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $signupChart->setData([
            'labels' => $signupLabels,
            'datasets' => [
                [
                    'label' => 'Nouveaux utilisateurs inscrits',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'data' => $signupData,
                ],
            ],
        ]);


        $customerCount = 0;
        $transactionsCount = 0;
        $companyRevenue = 0;
        $latestPayments = [];
        $latestCustomers = [];
        $customerGrowthRate = 0;
        $transactionsGrowthRate = 0;
        $companyRevenueGrowthRate = 0;


        if ($company !== null) {
            $customerCount = $this->customerRepository->countByCompany($company);
            $transactionsCount = $this->paymentRepository->countTransactionsByCompany($company);
            $companyRevenue = $this->paymentRepository->calculateRevenueByCompany($company);
            $latestPayments = $this->paymentRepository->findLatestPaymentsForCompany($company, 7);
            $latestCustomers = $this->customerRepository->findLatestCustomersForCompany($company, 5);
            $customerGrowthRate = $this->customerRepository->findGrowthRateCustomersByMonthForCompany($company);
            $transactionsGrowthRate = $this->paymentRepository->findGrowthRateTransactionsByMonthForCompany($company);
            $companyRevenueGrowthRate = $this->paymentRepository->findGrowthRatePaymentsByMonthForCompany($company);
        }

        $transactions = [];
        foreach ($latestPayments as $payment) {
            $transactions[] = [
                'description' => 'Paiement de facture de',
                'entity' => $payment->getInvoice()->getCustomer()->getFirstName() . ' ' . $payment->getInvoice()->getCustomer()->getLastName(),
                'date' => $payment->getCreatedAt()->format('M d, Y'),
                'amount' => $payment->getAmount(),
            ];
        }
        return $this->render('back/dashboard/company_dashboard.html.twig', [
            'customerCount' => $customerCount,
            'transactionsCount' => $transactionsCount,
            'companyRevenue' => $companyRevenue,
            'company' => $company,
            'paymentChart' => $paymentChart,
            'signupChart' => $signupChart,
            'transactions' => $transactions,
            'latestCustomers' => $latestCustomers,
            'customerGrowthRate' => $customerGrowthRate,
            'transactionsGrowthRate' => $transactionsGrowthRate,
            'companyRevenueGrowthRate' => $companyRevenueGrowthRate,
        ]);
    }
}
