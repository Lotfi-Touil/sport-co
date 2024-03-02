<?php
namespace App\Controller\Back;

use App\Repository\ReportRepository;
use App\Service\PDFExportService;
use App\Service\ReportGenerationService;
use App\Service\UserService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ReportController extends AbstractController
{
    private ReportGenerationService $reportGenerationService;
    private PDFExportService $pdfExportService;
    private ReportRepository $reportRepository;
    private Security $security;
    private UserService $userService;

    private EntityManagerInterface $entityManager;

    public function __construct(ReportGenerationService $reportGenerationService, PDFExportService $pdfExportService, ReportRepository $reportRepository, Security $security, UserService $userService, EntityManagerInterface $entityManager)
    {
        $this->reportGenerationService = $reportGenerationService;
        $this->pdfExportService = $pdfExportService;
        $this->reportRepository = $reportRepository;
        $this->userService = $userService;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    #[Route('/platform/report', name: 'platform_report')]
    public function listReports(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $reports = $this->reportRepository->findAll();
        } elseif ($this->isGranted('ROLE_COMPANY')) {
            $company = $this->userService->getCurrentUserCompany();
            $reports = $this->reportRepository->findBy(['company' => $company]);
        } else {
            throw new AccessDeniedException('Vous n\'avez pas l\'autorisation d\'accéder à cette page.');
        }

        return $this->render('back/report/list.html.twig', [
            'reports' => $reports,
        ]);
    }

    #[Route('/report/delete/{reportId}', name: 'delete_report')]
    public function deleteReport(int $reportId): Response
    {
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
    public function generateReport(): Response {
        if ($this->isGranted('ROLE_COMPANY')) {
            $company = $this->userService->getCurrentUserCompany();
            if (!$company) {
                throw $this->createNotFoundException('Aucune entreprise trouvée pour cet utilisateur.');
            }
            $this->reportGenerationService->generateForCompany($company);
        } elseif ($this->isGranted('ROLE_ADMIN')) {
            $this->reportGenerationService->generateGlobalReport();
        } else {
            throw $this->createAccessDeniedException('Accès non autorisé.');
        }

        return $this->redirectToRoute('platform_report');
    }

    #[Route('/report/view/{reportId}', name: 'report_view')]
    public function viewReport(int $reportId): Response {
        $report = $this->reportRepository->find($reportId);
        if (!$report) {
            throw $this->createNotFoundException('Le rapport demandé n\'existe pas.');
        }

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
    public function exportReport(int $reportId): Response
    {
        $report = $this->reportRepository->find($reportId);
        if (!$report) {
            throw $this->createNotFoundException('Le rapport demandé n\'existe pas.');
        }

        $pdfContent = $this->pdfExportService->exportReportToPDF($report);

        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="report_' . $reportId . '.pdf"');

        return $response;
    }
}
