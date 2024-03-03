<?php

namespace App\Controller\Back;

use App\Entity\QuoteStatus;
use App\Form\QuoteStatusType;
use App\Repository\QuoteStatusRepository;
use App\Service\PageAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/platform/quote-status')]
class QuoteStatusController extends AbstractController
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

    #[Route('/', name: 'platform_quote_status_index', methods: ['GET'])]
    public function index(Request $request, QuoteStatusRepository $quoteStatusRepository): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($this->authorizationChecker->isGranted("ROLE_ADMIN")) {
            $quote_statuses = $quoteStatusRepository->findAll();
        } else {
            $company = $this->security->getUser()->getCompany();
            if ($company) {
                $quote_statuses = $quoteStatusRepository->findAllByCompanyId($company->getId());
            }
        }

        return $this->render('back/quote_status/index.html.twig', [
            'quote_statuses' => $quote_statuses,
        ]);
    }

    #[Route('/new', name: 'platform_quote_status_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $quoteStatus = new QuoteStatus();
        $form = $this->createForm(QuoteStatusType::class, $quoteStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->security->getUser();
            $quoteStatus->setCompany($user->getCompany());
            $entityManager->persist($quoteStatus);
            $entityManager->flush();

            return $this->redirectToRoute('platform_quote_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/quote_status/new.html.twig', [
            'quote_status' => $quoteStatus,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'platform_quote_status_show', methods: ['GET'])]
    public function show(Request $request, QuoteStatus $quoteStatus): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));
        $response = $this->checkConfidentiality($quoteStatus);
        if ($response !== null) {
            return $response; // Redirection si l'utilisateur n'a pas accès
        }

        return $this->render('back/quote_status/show.html.twig', [
            'quote_status' => $quoteStatus,
        ]);
    }

    #[Route('/{id}/edit', name: 'platform_quote_status_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, QuoteStatus $quoteStatus, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));
        $response = $this->checkConfidentiality($quoteStatus);
        if ($response !== null) {
            return $response; // Redirection si l'utilisateur n'a pas accès
        }

        $form = $this->createForm(QuoteStatusType::class, $quoteStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('platform_quote_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/quote_status/edit.html.twig', [
            'quote_status' => $quoteStatus,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'platform_quote_status_delete', methods: ['POST'])]
    public function delete(Request $request, QuoteStatus $quoteStatus, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));
        $response = $this->checkConfidentiality($quoteStatus);
        if ($response !== null) {
            return $response; // Redirection si l'utilisateur n'a pas accès
        }

        if ($this->isCsrfTokenValid('delete'.$quoteStatus->getId(), $request->request->get('_token'))) {
            $entityManager->remove($quoteStatus);
            $entityManager->flush();
        }

        return $this->redirectToRoute('platform_quote_status_index', [], Response::HTTP_SEE_OTHER);
    }

    private function checkConfidentiality(QuoteStatus $quoteStatus): ?Response
    {
        if ($this->authorizationChecker->isGranted("ROLE_ADMIN")) {
            return null; // L'admin a accès à tout, donc pas de redirection
        }
    
        if ($this->security->getUser()->getCompany() == $quoteStatus->getCompany()) {
            return null; // L'utilisateur a le droit d'accéder à cette ressource
        }
    
        // L'utilisateur n'a pas le droit d'accéder à cette ressource
        $this->addFlash('error', "Accès non autorisé à la ressource demandée.");
        return new RedirectResponse($this->generateUrl('platform_quote_status_index'));
    }

}
