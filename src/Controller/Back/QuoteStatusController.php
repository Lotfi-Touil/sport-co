<?php

namespace App\Controller\Back;

use App\Entity\QuoteStatus;
use App\Form\QuoteStatusType;
use App\Repository\QuoteStatusRepository;
use App\Service\PageAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/platform/quote-status')]
class QuoteStatusController extends AbstractController
{
    private $pageAccessService;

    public function __construct(PageAccessService $pageAccessService)
    {
        $this->pageAccessService = $pageAccessService;
    }

    #[Route('/', name: 'platform_quote_status_index', methods: ['GET'])]
    public function index(Request $request, QuoteStatusRepository $quoteStatusRepository): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        return $this->render('back/quote_status/index.html.twig', [
            'quote_statuses' => $quoteStatusRepository->findAll(),
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

        return $this->render('back/quote_status/show.html.twig', [
            'quote_status' => $quoteStatus,
        ]);
    }

    #[Route('/{id}/edit', name: 'platform_quote_status_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, QuoteStatus $quoteStatus, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

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

        if ($this->isCsrfTokenValid('delete'.$quoteStatus->getId(), $request->request->get('_token'))) {
            $entityManager->remove($quoteStatus);
            $entityManager->flush();
        }

        return $this->redirectToRoute('platform_quote_status_index', [], Response::HTTP_SEE_OTHER);
    }
}
