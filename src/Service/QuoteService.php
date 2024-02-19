<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\Quote;
use App\Entity\QuoteProduct;
use Doctrine\ORM\EntityManagerInterface;

class QuoteService
{
    private $entityManager;
    private $error;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function addError(string $error): void
    {
        $this->error = $error;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function update(Quote $quote, array $params): bool
    {
        $quoteProductsData = $this->decodeQuoteProductsData($params);

        if (!$quoteProductsData) {
            $this->addError("Le devis ne contient aucun produit.");
            return false;
        }

        $existingQuoteProducts = $this->mapExistingQuoteProducts($quote);

        if (!$this->processQuoteProducts($quote, $quoteProductsData, $existingQuoteProducts)) {
            return false;
        }

        $this->removeUnmatchedQuoteProducts($quote, $quoteProductsData, $existingQuoteProducts);

        $this->entityManager->flush();
        return true;
    }

    private function decodeQuoteProductsData(array $params): ?array
    {
        $quoteProductsJson = $params['form']['products_json'];
        return json_decode($quoteProductsJson, true);
    }

    private function mapExistingQuoteProducts(Quote $quote): array
    {
        $existingQuoteProducts = [];
        foreach ($quote->getQuoteProducts() as $existingQuoteProduct) {
            $existingQuoteProducts[$existingQuoteProduct->getProduct()->getId()] = $existingQuoteProduct;
        }
        return $existingQuoteProducts;
    }

    private function processQuoteProducts(Quote $quote, array $quoteProductsData, array &$existingQuoteProducts): bool
    {
        $totalHT = $totalTaxes = $totalTTC = 0;

        foreach ($quoteProductsData as $productData) {
            if (!$this->processSingleQuoteProduct($quote, $productData, $existingQuoteProducts, $totalHT, $totalTaxes, $totalTTC)) {
                return false;
            }
        }

        $quote->setSubtotal($totalHT);
        $quote->setTotalAmount($totalTTC);
        return true;
    }

    private function processSingleQuoteProduct(Quote $quote, array $productData, array &$existingQuoteProducts, &$totalHT, &$totalTaxes, &$totalTTC): bool
    {
        $productId = $productData['product_id'];
        $product = $this->entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            $this->addError("Une erreur est survenue lors de la mise à jour du produit n°{$productData['id']} ({$product->getName()}).");
            return false;
        }

        if ($product->getPrice() != $productData['price'] || $product->getTaxRate() != $productData['tax_rate']) {
            $this->addError("Une erreur est survenue lors de la vérification des informations du produit n°{$product->getId()}.");
            return false;
        }

        $quantity = $productData['quantity'];
        $this->updateOrCreateQuoteProduct($quote, $product, $quantity, $existingQuoteProducts);

        $productTotalHT = $product->getPriceHT() * $quantity;
        $productTotalTaxes = $product->getTaxRate() * $quantity;
        $productTotalTTC = $product->getPrice() * $quantity;

        $totalHT += $productTotalHT;
        $totalTaxes += $productTotalTaxes;
        $totalTTC += $productTotalTTC;

        return true;
    }

    private function updateOrCreateQuoteProduct(Quote $quote, Product $product, int $quantity, array &$existingQuoteProducts): QuoteProduct
    {
        if (isset($existingQuoteProducts[$product->getId()])) {
            // Si le QuoteProduct existe déjà, on met à jour la quantité.
            $quoteProduct = $existingQuoteProducts[$product->getId()];
            $quoteProduct->setQuantity($quantity);
        } else {
            // Si le QuoteProduct n'existe pas, on en crée un nouveau.
            $quoteProduct = new QuoteProduct();
            $quoteProduct->setQuote($quote);
            $quoteProduct->setProduct($product);
            $quoteProduct->setQuantity($quantity);
            $quoteProduct->setPrice($product->getPrice());
            $quoteProduct->setTaxRate($product->getTaxRate());

            // Ajout du nouveau QuoteProduct au devis (Quote).
            $quote->addQuoteProduct($quoteProduct);
        }

        // Avant de retourner le QuoteProduct, on s'assure que toutes les propriétés non-null sont définies.
        if ($quoteProduct->getPrice() === null || $quoteProduct->getTaxRate() === null) {
            throw new \Exception("Le prix et le taux de taxe sont requis pour le QuoteProduct.");
        }

        return $quoteProduct;
    }

    private function removeUnmatchedQuoteProducts(Quote $quote, array $quoteProductsData, array $existingQuoteProducts): void
    {
        $idsProductFromPost = array_column($quoteProductsData, 'product_id');
        foreach ($existingQuoteProducts as $existingProductId => $existingQuoteProduct) {
            if (!in_array($existingProductId, $idsProductFromPost)) {
                $quote->removeQuoteProduct($existingQuoteProduct);
                $this->entityManager->remove($existingQuoteProduct);
            }
        }
    }

    public function create(Quote $quote, array $params): bool
    {
        $quoteProductsJson = $params['form']['products_json'];
        $quoteProductsData = json_decode($quoteProductsJson, true);

        if (!$quoteProductsData)
        {
            $this->addError('Oups! Le devis ne contient aucun article.');
            return false;
        }

        $repository = $this->entityManager->getRepository(Product::class);

        foreach ($quoteProductsData as $productData)
        {
            $product = $repository->find($productData['product_id']);
            if ($product)
            {
                if ($product->getPrice() != $productData['price'] || $product->getTaxRate() != $productData['tax_rate'])
                {
                    $this->addError("Une erreur est survenue lors de l'ajout du produit n°{$productData['id']} ({$product->getName()}).");
                    return false;
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

        $this->entityManager->persist($quote);
        $this->entityManager->flush();

        return true;
    }
}
