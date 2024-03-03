<?php

namespace App\Controller\Back;

use App\Entity\InvoiceStatus;
use App\Form\InvoiceStatusType;
use App\Repository\InvoiceStatusRepository;
use App\Service\PageAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/platform/invoice-status')]
class InvoiceStatusController extends AbstractController
{
    private $pageAccessService;
    private $security;
    private $authorizationChecker;

    public function __construct(PageAccessService $pageAccessService, Security $security, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->pageAccessService = $pageAccessService;
        $this->security = $security;
        $this->authorizationChecker = $authorizationChecker;
    }

    #[Route('/', name: 'platform_invoice_status_index', methods: ['GET'])]
    public function index(Request $request, InvoiceStatusRepository $invoiceStatusRepository): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($this->authorizationChecker->isGranted("ROLE_ADMIN")) {
            $invoice_statuses = $invoiceStatusRepository->findAll();
        } else {
            $company = $this->security->getUser()->getCompany();
            if ($company) {
                $invoice_statuses = $invoiceStatusRepository->findAllByCompanyId($company->getId());
            }
        }

        return $this->render('back/invoice_status/index.html.twig', [
            'invoice_statuses' => $invoice_statuses,
        ]);
    }

    #[Route('/new', name: 'platform_invoice_status_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $invoiceStatus = new InvoiceStatus();
        $form = $this->createForm(InvoiceStatusType::class, $invoiceStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->security->getUser();
            $invoiceStatus->setCompany($user->getCompany());
            $entityManager->persist($invoiceStatus);
            $entityManager->flush();

            return $this->redirectToRoute('platform_invoice_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/invoice_status/new.html.twig', [
            'invoice_status' => $invoiceStatus,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'platform_invoice_status_show', methods: ['GET'])]
    public function show(Request $request, InvoiceStatus $invoiceStatus): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));
        $response = $this->checkConfidentiality($invoiceStatus);
        if ($response !== null) {
            return $response; // Redirection si l'utilisateur n'a pas accès
        }

        return $this->render('back/invoice_status/show.html.twig', [
            'invoice_status' => $invoiceStatus,
        ]);
    }

    #[Route('/{id}/edit', name: 'platform_invoice_status_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, InvoiceStatus $invoiceStatus, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));
        $response = $this->checkConfidentiality($invoiceStatus);
        if ($response !== null) {
            return $response; // Redirection si l'utilisateur n'a pas accès
        }
 
        $form = $this->createForm(InvoiceStatusType::class, $invoiceStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('platform_invoice_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/invoice_status/edit.html.twig', [
            'invoice_status' => $invoiceStatus,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'platform_invoice_status_delete', methods: ['POST'])]
    public function delete(Request $request, InvoiceStatus $invoiceStatus, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));
        $response = $this->checkConfidentiality($invoiceStatus);
        if ($response !== null) {
            return $response; // Redirection si l'utilisateur n'a pas accès
        }

        if ($this->isCsrfTokenValid('delete'.$invoiceStatus->getId(), $request->request->get('_token'))) {
            $entityManager->remove($invoiceStatus);
            $entityManager->flush();
        }

        return $this->redirectToRoute('platform_invoice_status_index', [], Response::HTTP_SEE_OTHER);
    }

    private function checkConfidentiality(InvoiceStatus $invoiceStatus): ?Response
    {
        if ($this->authorizationChecker->isGranted("ROLE_ADMIN")) {
            return null; // L'admin a accès à tout, donc pas de redirection
        }
    
        if ($this->security->getUser()->getCompany() == $invoiceStatus->getCompany()) {
            return null; // L'utilisateur a le droit d'accéder à cette ressource
        }
    
        // L'utilisateur n'a pas le droit d'accéder à cette ressource
        $this->addFlash('error', "Accès non autorisé à la ressource demandée.");
        return new RedirectResponse($this->generateUrl('platform_invoice_status_index'));
    }

}
