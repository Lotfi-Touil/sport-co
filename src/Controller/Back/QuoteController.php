<?php

namespace App\Controller\Back;

use App\Entity\Product;
use App\Entity\Quote;
use App\Entity\QuoteProduct;
use App\Entity\QuoteStatus;
use App\Form\QuoteType;
use App\Repository\QuoteRepository;
use App\Repository\QuoteStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/platform/quote')]
class QuoteController extends AbstractController
{
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
        $form = $this->createForm(QuoteType::class, $quote, ['status_choices' => $statusList, 'entityManager' => $entityManager]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $params = $request->request->all();

            $quoteProductsJson = $params['form']['products_json'];
            $quoteProductsData = json_decode($quoteProductsJson, true);

            if ($quoteProductsData)
            {
                $repository = $entityManager->getRepository(Product::class);

                foreach ($quoteProductsData as $productData) {
                    $product = $repository->find($productData['id']);
                    if ($product) {
                        if ($product->getPrice() != $productData['price'] || $product->getTaxRate() != $productData['tax_rate']) {
                            $form->addError(new FormError("Une erreur est survenue lors de l'ajout du produit n°{$productData['id']} ({$product->getName()})."));
                            return $this->render('back/quote/new.html.twig', [
                                'quote' => $quote,
                                'form' => $form->createView(),
                            ]);
                        }

                        $quantity = $productData['quantity'];

                        // Créer une nouvelle instance de QuoteProduct ou utiliser une entité de jointure appropriée
                        $quoteProduct = new QuoteProduct();
                        $quoteProduct->setQuote($quote);
                        $quoteProduct->setProduct($product);
                        $quoteProduct->setQuantity($quantity);
                        $quoteProduct->setPrice($product->getPrice());
                        $quoteProduct->setTaxRate($product->getTaxRate());

                        // Ajouter cette entité de jointure à votre entité Quote
                        $quote->addQuoteProduct($quoteProduct);
                        $quote->incrementSubtotal($product->getPriceHT() * $quantity);
                        $quote->incrementTotalAmount($product->getPrice() * $quantity);
                    }
                }
            }

            $entityManager->persist($quote);
            $entityManager->flush();

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
