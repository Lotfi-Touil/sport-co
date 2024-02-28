<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\Quote;
use App\Entity\QuoteProduct;
use App\Entity\QuoteUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class QuoteService
{
    private Security $security;
    private EntityManagerInterface $entityManager;

    private $error;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
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

        $quoteCustomerData = $this->decodeCustomerData($params);
        $existingQuoteUser = $this->getExistingQuoteUser($quote);

        if (!$this->processCustomer($quote, $quoteCustomerData, $existingQuoteUser)) {
            return false;
        }

        $this->removeUnmatchedQuoteProducts($quote, $quoteProductsData, $existingQuoteProducts);

        $this->entityManager->flush();
        return true;
    }

    private function decodeCustomerData(array $params): ?array
    {
        $quoteCustomerJson = $params['form']['customer_json'];
        return json_decode($quoteCustomerJson, true);
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

    private function getExistingQuoteUser(Quote $quote): ?QuoteUser
    {
        return $this->entityManager->getRepository(QuoteUser::class)->findByQuoteId($quote->getId());
    }

    private function processCustomer(Quote $quote, array $customerData, ?QuoteUser $existingQuoteUser): bool
    {
        $customerId = $customerData['id'];

        if (!$customerId) { // Devis soumis sans destinataire
            if ($existingQuoteUser) {
                $this->entityManager->remove($existingQuoteUser);
            }
            return true;
        }

        $customer = $this->entityManager->getRepository(Customer::class)->find($customerId);

        if (!$customer) {
            $this->addError("Une erreur est survenue lors de l'enregistrement du destinataire.");
            return false;
        }

        // Voir s'il y a eu une tentative d'inspecter l'élément ^^
        if ($customer->getEmail() != $customerData['email']) {
            $this->addError("Une erreur est survenue lors de la vérification des informations.");
            return false;
        }

        $this->updateOrCreateQuoteUser($quote, $customer, $existingQuoteUser);

        return true;
    }

    private function updateOrCreateQuoteUser(Quote $quote, Customer $customer, ?QuoteUser &$existingQuoteUser): QuoteUser
    {
        $creator = $this->security->getUser();

        if (!$creator) {
            throw new \RuntimeException('Aucun utilisateur connecté.');
        }

        if ($existingQuoteUser) {
            // Si QuoteUser existe déjà, on met à jour.
            $quoteUser = $existingQuoteUser;

            $quoteUser->setCustomer($customer);
            $quoteUser->setCreator($creator);
        } else {
            // Si QuoteUser n'existe pas, on en crée un nouveau.
            $quoteUser = new QuoteUser();

            $quoteUser->setQuote($quote);
            $quoteUser->setCustomer($customer);
            $quoteUser->setCreator($creator);

            $quote->addQuoteUser($quoteUser);
        }

        return $quoteUser;
    }

    private function processQuoteProducts(Quote $quote, array $quoteProductsData, ?array $existingQuoteProducts): bool
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

    private function processSingleQuoteProduct(Quote $quote, array $productData, ?array $existingQuoteProducts, &$totalHT, &$totalTaxes, &$totalTTC): bool
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

    private function updateOrCreateQuoteProduct(Quote $quote, Product $product, int $quantity, ?array $existingQuoteProducts): QuoteProduct
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
        $quoteCustomerData = $this->decodeCustomerData($params);

        if (!$this->processCustomer($quote, $quoteCustomerData, null)) {
            return false;
        }

        $quoteProductsData = $this->decodeQuoteProductsData($params);

        if (!$quoteProductsData) {
            $this->addError("Le devis ne contient aucun produit.");
            return false;
        }

        if (!$this->processQuoteProducts($quote, $quoteProductsData, null)) {
            return false;
        }

        $this->entityManager->persist($quote);
        $this->entityManager->flush();

        return true;
    }

}
