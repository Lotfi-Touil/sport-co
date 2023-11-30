<?php

namespace App\Controller\Back;

use App\Entity\Quote;
use App\Form\QuoteType;
use App\Repository\QuoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/platform/quote')]
class QuoteController extends AbstractController
{
    #[Route('/', name: 'platform_quote_index', methods: ['GET'])]
    public function index(QuoteRepository $quoteRepository): Response
    {
        return $this->render('back/quote/index.html.twig', [
            'quotes' => $quoteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'platform_quote_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $quote = new Quote();
        $form = $this->createForm(QuoteType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($quote);
            $entityManager->flush();

            return $this->redirectToRoute('platform_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/quote/new.html.twig', [
            'quote' => $quote,
            'form' => $form,
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
        $form = $this->createForm(QuoteType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('platform_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/quote/edit.html.twig', [
            'quote' => $quote,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'platform_quote_delete', methods: ['POST'])]
    public function delete(Request $request, Quote $quote, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quote->getId(), $request->request->get('_token'))) {
            $entityManager->remove($quote);
            $entityManager->flush();
        }

        return $this->redirectToRoute('platform_quote_index', [], Response::HTTP_SEE_OTHER);
    }
}
