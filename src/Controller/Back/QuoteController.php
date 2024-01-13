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
        $form = $this->createForm(QuoteType::class, $quote, ['status_choices' => $statusList]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $params = $request->request->all();

            $quoteProductsJson = $params['form']['products_json'];
            $quoteProductsData = json_decode($quoteProductsJson, true);

            if (!$quoteProductsData)
            {
                $errorMessage = "Une erreur est survenue lors de l'envoi des données.";

                $form->addError(new FormError($errorMessage));
                $this->addFlash('error', $errorMessage);

                return $this->render('back/quote/new.html.twig', [
                    'quote' => $quote,
                    'form' => $form->createView(),
                ]);
            }

            $repository = $entityManager->getRepository(Product::class);

            foreach ($quoteProductsData as $productData) {
                $product = $repository->find($productData['product_id']);
                if ($product) {
                    if ($product->getPrice() != $productData['price'] || $product->getTaxRate() != $productData['tax_rate']) {
                        $errorMessage = "Une erreur est survenue lors de l'ajout du produit n°{$productData['id']} ({$product->getName()}).";

                        $form->addError(new FormError($errorMessage));
                        $this->addFlash('error', $errorMessage);

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

            $entityManager->persist($quote);
            $entityManager->flush();

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

        // TODO Lotfi : faire une classe dédiée au traitement de l'update car l'action est beaucoup trop longue. QuoteManager
        if ($form->isSubmitted() && $form->isValid()) {
            $params = $request->request->all();

            $quoteProductsJson = $params['form']['products_json'];
            $quoteProductsData = json_decode($quoteProductsJson, true);

            // Map existants QuoteProduct par productId pour un accès facile
            $existingQuoteProducts = [];
            foreach ($quote->getQuoteProducts() as $existingQuoteProduct) {
                $existingQuoteProducts[$existingQuoteProduct->getProduct()->getId()] = $existingQuoteProduct;
            }

            // Initialisation des totaux
            $totalHT = 0;
            $totalTaxes = 0;
            $totalTTC = 0;

            // On reçoit bien les données du formulaire
            if ($quoteProductsData)
            {
                // On parcours chaque article du formulaire
                foreach ($quoteProductsData as $productData)
                {
                    $productId = $productData['product_id'];
                    $product = $entityManager->getRepository(Product::class)->find($productId);

                    if (!$product)
                    {
                        $errorMessage = "Une erreur est survenue lors de la mise à jour du produit n°{$productData['id']} ({$product->getName()}).";

                        $form->addError(new FormError($errorMessage));
                        $this->addFlash('error', $errorMessage);

                        return $this->render('back/quote/edit.html.twig', [
                            'quote' => $quote,
                            'form' => $form->createView(),
                        ]);
                    }

                    if ($product->getPrice() != $productData['price'] || $product->getTaxRate() != $productData['tax_rate'])
                    {
                        $errorMessage = "Une erreur est survenue lors de la mise à jour du produit n°{$product->getId()} ({$product->getName()}).";

                        $form->addError(new FormError($errorMessage));
                        $this->addFlash('error', $errorMessage);

                        return $this->render('back/quote/edit.html.twig', [
                            'quote' => $quote,
                            'form' => $form->createView(),
                        ]);
                    }

                    $quantity = $productData['quantity'];

                    // Vérifier si le produit existe déjà dans le devis
                    if (isset($existingQuoteProducts[$product->getId()])) {
                        // Mettre à jour le QuoteProduct existant
                        $quoteProduct = $existingQuoteProducts[$product->getId()];
                        $quoteProduct->setQuantity($quantity);
                    } else {
                        // Créer un nouveau QuoteProduct si le produit n'existe pas
                        $quoteProduct = new QuoteProduct();
                        $quoteProduct->setQuote($quote);
                        $quoteProduct->setProduct($product);
                        $quoteProduct->setQuantity($quantity);
                        $quoteProduct->setPrice($product->getPrice());
                        $quoteProduct->setTaxRate($product->getTaxRate());

                        // Ajouter le nouveau QuoteProduct au Quote
                        $quote->addQuoteProduct($quoteProduct);
                    }

                    // Calcul des totaux
                    $priceTTC = $product->getPrice();
                    $priceHT = $product->getPriceHT();
                    $taxRate = $product->getTaxRate();

                    $productTotalHT = $priceHT * $quantity;
                    $productTotalTaxes = ($priceHT * ($taxRate / 100)) * $quantity;
                    $productTotalTTC = $priceTTC * $quantity;

                    $totalHT += $productTotalHT;
                    $totalTaxes += $productTotalTaxes;
                    $totalTTC += $productTotalTTC;
                }
            }

            $idsProductFromPost = array_column($quoteProductsData, 'product_id');

            // Suppression des QuoteProducts qui ne sont plus dans le nouveau tableau de produits
            foreach ($existingQuoteProducts as $existingProductId => $existingQuoteProduct) {
                if (!in_array($existingProductId, $idsProductFromPost)) {
                    $quote->removeQuoteProduct($existingQuoteProduct);
                    $entityManager->remove($existingQuoteProduct);
                }
            }

            // Mise à jour des totaux sur l'entité Quote
            $quote->setSubtotal($totalHT);
            $quote->setTotalAmount($totalTTC);

            $entityManager->flush();
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
