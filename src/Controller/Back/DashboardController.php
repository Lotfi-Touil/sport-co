<?php

namespace App\Controller\Back;

use App\Service\PageAccessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use App\Service\DashboardDataService;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DashboardController extends AbstractController
{
    private DashboardDataService $dashboardDataService;
    private $pageAccessService;

    public function __construct(PageAccessService $pageAccessService, DashboardDataService $dashboardDataService)
    {
        $this->dashboardDataService = $dashboardDataService;
        $this->pageAccessService = $pageAccessService;
    }

    #[Route('/platform/dashboard', name: 'platform_dashboard')]
    public function index(Request $request, AuthorizationCheckerInterface $authorizationChecker, ChartBuilderInterface $chartBuilder): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($authorizationChecker->isGranted('ROLE_ADMIN')) {
            return $this->adminDashboard($chartBuilder);
        } else {
            return $this->companyDashboard($chartBuilder);
        }

        throw new AccessDeniedHttpException('Accès refusé.');
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

        $monthNamesFR = [
            0 => 'Mois inconnu',
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars',
            4 => 'Avril',   5 => 'Mai',      6 => 'Juin',
            7 => 'Juillet', 8 => 'Août',     9 => 'Septembre',
            10 => 'Octobre',11 => 'Novembre',12 => 'Décembre',
        ];

        $labels = array_map(function ($entry) use ($monthNamesFR) {
            if (isset($entry['month']) && is_numeric($entry['month']) && (int)$entry['month'] >= 1 && (int)$entry['month'] <= 12) {
                return $monthNamesFR[(int)$entry['month']];
            } else {
                return $monthNamesFR[$entry['month']] ?? $entry['month'];
            }
        }, $data);

        $values = array_column($data, $type === Chart::TYPE_BAR ? 'count' : 'total');

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
