<?php

namespace App\Controller\Back;

use App\Entity\Product;
use App\Entity\Quote;
use App\Entity\QuoteProduct;
use App\Entity\QuoteStatus;
use App\Entity\User;
use App\Form\QuoteType;
use App\Repository\QuoteRepository;
use App\Repository\QuoteStatusRepository;
use App\Service\QuoteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/platform/quote')]
class QuoteController extends AbstractController
{
    private $quoteService;

    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    #[Route('/', name: 'platform_quote_index', methods: ['GET'])]
    public function index(QuoteRepository $quoteRepository, QuoteStatusRepository $quoteStatusRepository): Response
    {
        return $this->render('back/quote/index.html.twig', [
            'quotes' => $quoteRepository->findAll(),
            'quote_status' => $quoteStatusRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'platform_quote_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $statusList = $entityManager->getRepository(QuoteStatus::class)->findAll();

        $quote = new Quote();
        $form = $this->createForm(QuoteType::class, $quote, ['status_choices' => $statusList]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $params = $request->request->all();

            if (!$this->quoteService->create($quote, $params))
            {
                $this->addFlash('error', $this->quoteService->getError());

                return $this->render('back/quote/new.html.twig', [
                    'quote' => $quote,
                    'form' => $form->createView(),
                ]);
            }

            $this->addFlash('success', 'Le devis a été enregistré avec succès.');
            return $this->redirectToRoute('platform_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/quote/new.html.twig', [
            'quote' => $quote,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'platform_quote_show', methods: ['GET'])]
    public function show(Quote $quote): Response
    {
        return $this->render('back/quote/show.html.twig', [
            'quote' => $quote,
        ]);
    }

    #[Route('/{id}/edit', name: 'platform_quote_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quote $quote, EntityManagerInterface $entityManager): Response
    {
        $statusList = $entityManager->getRepository(QuoteStatus::class)->findAll();
        $form = $this->createForm(QuoteType::class, $quote, ['status_choices' => $statusList]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $params = $request->request->all();

            if (!$this->quoteService->update($quote, $params))
            {
                $this->addFlash('error', $this->quoteService->getError());

                return $this->render('back/quote/edit.html.twig', [
                    'quote' => $quote,
                    'form' => $form->createView(),
                ]);
            }

            $this->addFlash('success', 'Le devis a été mis à jour avec succès.');
            return $this->redirectToRoute('platform_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/quote/edit.html.twig', [
            'quote' => $quote,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'platform_quote_delete', methods: ['POST'])]
    public function delete(Request $request, Quote $quote, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quote->getId(), $request->request->get('_token'))) {
            foreach ($quote->getQuoteProducts() as $quoteProduct) {
                $entityManager->remove($quoteProduct);
            }

            $entityManager->remove($quote);
            $entityManager->flush();

            $this->addFlash('success', 'Le devis a été supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('platform_quote_index', [], Response::HTTP_SEE_OTHER);
    }
}
