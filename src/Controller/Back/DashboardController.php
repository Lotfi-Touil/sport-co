<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractController
{
    #[Route('/platform/dashboard', name: 'platform_dashboard')]
    public function dashboard(ChartBuilderInterface $chartBuilder, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        $paymentChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $paymentChart->setData([
            'labels' => ['Janvier', 'Février', 'Mars', 'Avril'],
            'datasets' => [
                [
                    'label' => 'Total de paiements',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'data' => [12000, 19000, 3000, 5000],
                ],
            ],
        ]);

        $signupChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $signupChart->setData([
            'labels' => ['Janvier', 'Février', 'Mars', 'Avril'],
            'datasets' => [
                [
                    'label' => 'Nouveaux utilisateurs inscrits',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'data' => [50, 25, 75, 100],
                ],
            ],
        ]);

        $isUserAdmin = $authorizationChecker->isGranted('ROLE_ADMIN');
        $isUserCompany = $authorizationChecker->isGranted('ROLE_COMPANY'); // Assurez-vous que ROLE_COMPANY est bien défini dans votre système

        return $this->render('back/dashboard/index.html.twig', [
            'paymentChart' => $paymentChart,
            'signupChart' => $signupChart,
            'isUserAdmin' => $isUserAdmin,
            'isUserCompany' => $isUserCompany,
        ]);
    }
}