<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use App\Service\DashboardDataService;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractController
{
    private DashboardDataService $dashboardDataService;

    public function __construct(DashboardDataService $dashboardDataService)
    {
        $this->dashboardDataService = $dashboardDataService;
    }

    #[Route('/platform/dashboard', name: 'platform_dashboard')]
    public function index(AuthorizationCheckerInterface $authorizationChecker, ChartBuilderInterface $chartBuilder): Response
    {
        if ($authorizationChecker->isGranted('ROLE_ADMIN')) {
            return $this->adminDashboard($chartBuilder);
        } elseif ($authorizationChecker->isGranted('ROLE_COMPANY')) {
            return $this->companyDashboard($chartBuilder);
        }

        return $this->render('some_default_or_error_template.html.twig');
    }

    private function adminDashboard(ChartBuilderInterface $chartBuilder): Response
    {
        $data = $this->dashboardDataService->prepareDataForAdmin();
        $paymentChart = $this->createChart($chartBuilder, $data['paymentsByMonth'], 'Total de paiements');
        $signupChart = $this->createChart($chartBuilder, $data['signupsByMonth'], 'Nouveaux utilisateurs inscrits', Chart::TYPE_BAR);

        return $this->render('back/dashboard/admin_dashboard.html.twig', array_merge($data, [
            'paymentChart' => $paymentChart,
            'signupChart' => $signupChart,
        ]));
    }

    private function companyDashboard(ChartBuilderInterface $chartBuilder): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw new \LogicException('No user found');
        }
        $company = $user->getCompany();
        $data = $this->dashboardDataService->prepareDataForCompany($company);
        $paymentChart = $this->createChart($chartBuilder, $data['paymentsByMonth'], 'Total de paiements');
        $signupChart = $this->createChart($chartBuilder, $data['signupsByMonth'], 'Nouveaux utilisateurs inscrits', Chart::TYPE_BAR);

        return $this->render('back/dashboard/company_dashboard.html.twig', array_merge(['company' => $company], $data, [
            'paymentChart' => $paymentChart,
            'signupChart' => $signupChart,
        ]));
    }

    private function createChart(ChartBuilderInterface $chartBuilder, array $data, string $label, string $type = Chart::TYPE_LINE): Chart
    {
        $labels = array_column($data, 'month');
        $values = array_column($data, $type === Chart::TYPE_BAR ? 'count' : 'total'); // Utiliser 'count' pour les inscriptions

        $chart = $chartBuilder->createChart($type);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'data' => $values,
                ],
            ],
        ]);

        return $chart;
    }
}