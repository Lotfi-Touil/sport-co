<?php
namespace App\Controller\Back;

use App\Entity\PaymentStatus;
use App\Repository\ReportRepository;
use App\Service\PageAccessService;
use App\Service\PDFExportService;
use App\Service\ReportGenerationService;
use App\Service\UserService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ReportController extends AbstractController
{
    private $pageAccessService;

    private ReportGenerationService $reportGenerationService;
    private PDFExportService $pdfExportService;
    private ReportRepository $reportRepository;
    private Security $security;
    private UserService $userService;

    private EntityManagerInterface $entityManager;

    public function __construct(PageAccessService $pageAccessService, ReportGenerationService $reportGenerationService, PDFExportService $pdfExportService, ReportRepository $reportRepository, Security $security, UserService $userService, EntityManagerInterface $entityManager)
    {
        $this->pageAccessService = $pageAccessService;
        $this->reportGenerationService = $reportGenerationService;
        $this->pdfExportService = $pdfExportService;
        $this->reportRepository = $reportRepository;
        $this->userService = $userService;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    #[Route('/platform/report', name: 'platform_report')]
    public function listReports(Request $request): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($this->isGranted('ROLE_ADMIN')) {
            $reports = $this->reportRepository->findAll();
        } else {
            $company = $this->userService->getCurrentUserCompany();
            $reports = $this->reportRepository->findBy(['company' => $company]);
        }

        return $this->render('back/report/list.html.twig', [
            'reports' => $reports,
        ]);
    }

    #[Route('/report/delete/{reportId}', name: 'delete_report')]
    public function deleteReport(Request $request, int $reportId): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $report = $this->reportRepository->find($reportId);
        if (!$report) {
            $this->addFlash('error', 'Le rapport demandé n\'existe pas.');
            return $this->redirectToRoute('platform_report');
        }

        $this->entityManager->remove($report);
        $this->entityManager->flush();

        $this->addFlash('success', 'Le rapport a été supprimé avec succès.');
        return $this->redirectToRoute('platform_report');
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/report/generate', name: 'generate_report')]
    public function generateReport(Request $request): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($this->isGranted('ROLE_ADMIN')) {
            $this->reportGenerationService->generateGlobalReport();
        } else {
            $company = $this->userService->getCurrentUserCompany();
            if (!$company) {
                throw $this->createNotFoundException('Aucune entreprise trouvée pour cet utilisateur.');
            }
            $this->reportGenerationService->generateForCompany($company);
        }

        return $this->redirectToRoute('platform_report');
    }

//    #[Route('/report/view/{reportId}', name: 'report_view')]
//    public function viewReport(Request $request, int $reportId): Response
//    {
//        $this->pageAccessService->checkAccess($request->attributes->get('_route'));
//
//        $report = $this->reportRepository->find($reportId);
//        if (!$report) {
//            throw $this->createNotFoundException('Le rapport demandé n\'existe pas.');
//        }
//
//        return $this->render('back/report/view.html.twig', [
//            'report' => $report,
//        ]);
//    }

    #[Route('/report/view/{reportId}', name: 'report_view')]
    public function viewReport(Request $request, int $reportId): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $report = $this->reportRepository->find($reportId);
        if (!$report) {
            throw $this->createNotFoundException('Le rapport demandé n\'existe pas.');
        }

        $paymentDetails = json_decode($report->getPaymentDetails(), true);

        foreach ($paymentDetails as &$detail) {
            if (array_key_exists('paymentStatusId', $detail)) {
                $paymentStatus = $this->entityManager->getRepository(PaymentStatus::class)->find($detail['paymentStatusId']);
                $detail['paymentStatusName'] = $paymentStatus ? $paymentStatus->getName() : 'Inconnu';
            } else {
                $detail['paymentStatusName'] = 'Non spécifié';
            }
        }

        $report->setPaymentDetails(json_encode($paymentDetails));

        return $this->render('back/report/view.html.twig', [
            'report' => $report,
        ]);
    }



    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route('/report/export/{reportId}', name: 'export_report')]
    public function exportReport(Request $request, int $reportId): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $report = $this->reportRepository->find($reportId);
        if (!$report) {
            throw $this->createNotFoundException('Le rapport demandé n\'existe pas.');
        }

        $paymentDetails = json_decode($report->getPaymentDetails(), true);

        foreach ($paymentDetails as &$payment) {
            // Vérifier d'abord si 'paymentStatusId' existe et n'est pas null
            if (isset($payment['paymentStatusId'])) {
                $paymentStatus = $this->entityManager->getRepository(PaymentStatus::class)->find($payment['paymentStatusId']);
                $payment['paymentStatusName'] = $paymentStatus ? $paymentStatus->getName() : 'Inconnu';
            } else {
                // Gérer le cas où 'paymentStatusId' n'existe pas ou est null
                $payment['paymentStatusName'] = 'Non spécifié';
            }
        }
        unset($payment); // Bonne pratique pour éviter les problèmes dans les boucles futures
        $report->setPaymentDetails(json_encode($paymentDetails));


        $pdfContent = $this->pdfExportService->exportReportToPDF($report);

        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="report_' . $reportId . '.pdf"');

        return $response;
    }

}
