<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\Invoice;
use App\Entity\InvoiceProduct;
use App\Entity\InvoiceUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class InvoiceService
{
    private Security $security;
    private EntityManagerInterface $entityManager;
    private Environment $twig;

    private $error;

    public function __construct(Environment $twig, Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->twig = $twig;
    }

    private function addError(string $error): void
    {
        $this->error = $error;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function update(Invoice $invoice, array $params): bool
    {
        $invoiceProductsData = $this->decodeInvoiceProductsData($params);

        if (!$invoiceProductsData) {
            $this->addError("La facture ne contient aucun produit.");
            return false;
        }

        $existingInvoiceProducts = $this->mapExistingInvoiceProducts($invoice);

        if (!$this->processInvoiceProducts($invoice, $invoiceProductsData, $existingInvoiceProducts)) {
            return false;
        }

        $invoiceCustomerData = $this->decodeCustomerData($params);
        $existingInvoiceUser = $this->getExistingInvoiceUser($invoice);

        if (!$this->processCustomer($invoice, $invoiceCustomerData, $existingInvoiceUser)) {
            return false;
        }

        $this->removeUnmatchedInvoiceProducts($invoice, $invoiceProductsData, $existingInvoiceProducts);

        $this->entityManager->flush();
        return true;
    }

    private function decodeCustomerData(array $params): ?array
    {
        $invoiceCustomerJson = $params['form']['customer_json'];
        return json_decode($invoiceCustomerJson, true);
    }

    private function decodeInvoiceProductsData(array $params): ?array
    {
        $invoiceProductsJson = $params['form']['products_json'];
        return json_decode($invoiceProductsJson, true);
    }

    private function mapExistingInvoiceProducts(Invoice $invoice): array
    {
        $existingInvoiceProducts = [];
        foreach ($invoice->getInvoiceProducts() as $existingInvoiceProduct) {
            $existingInvoiceProducts[$existingInvoiceProduct->getProduct()->getId()] = $existingInvoiceProduct;
        }
        return $existingInvoiceProducts;
    }

    private function getExistingInvoiceUser(Invoice $invoice): ?InvoiceUser
    {
        return $this->entityManager->getRepository(InvoiceUser::class)->findByInvoiceId($invoice->getId());
    }

    private function processCustomer(Invoice $invoice, array $customerData, ?InvoiceUser $existingInvoiceUser): bool
    {
        $customerId = $customerData['id'];

        if (!$customerId) { // Facture soumise sans destinataire
            if ($existingInvoiceUser) {
                $this->entityManager->remove($existingInvoiceUser);
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

        $this->updateOrCreateInvoiceUser($invoice, $customer, $existingInvoiceUser);

        return true;
    }

    private function updateOrCreateInvoiceUser(Invoice $invoice, Customer $customer, ?InvoiceUser &$existingInvoiceUser): InvoiceUser
    {
        $creator = $this->security->getUser();

        if (!$creator) {
            throw new \RuntimeException('Aucun utilisateur connecté.');
        }

        if ($existingInvoiceUser) {
            // Si InvoiceUser existe déjà, on met à jour.
            $invoiceUser = $existingInvoiceUser;

            $invoiceUser->setCustomer($customer);
            $invoiceUser->setCreator($creator);
        } else {
            // Si InvoiceUser n'existe pas, on en crée un nouveau.
            $invoiceUser = new InvoiceUser();

            $invoiceUser->setInvoice($invoice);
            $invoiceUser->setCustomer($customer);
            $invoiceUser->setCreator($creator);

            $invoice->addInvoiceUser($invoiceUser);
        }

        return $invoiceUser;
    }

    private function processInvoiceProducts(Invoice $invoice, array $invoiceProductsData, ?array $existingInvoiceProducts): bool
    {
        $totalHT = $totalTaxes = $totalTTC = 0;

        foreach ($invoiceProductsData as $productData) {
            if (!$this->processSingleInvoiceProduct($invoice, $productData, $existingInvoiceProducts, $totalHT, $totalTaxes, $totalTTC)) {
                return false;
            }
        }

        $invoice->setSubtotal($totalHT);
        $invoice->setTotalAmount($totalTTC);
        return true;
    }

    private function processSingleInvoiceProduct(Invoice $invoice, array $productData, ?array $existingInvoiceProducts, &$totalHT, &$totalTaxes, &$totalTTC): bool
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
        $this->updateOrCreateInvoiceProduct($invoice, $product, $quantity, $existingInvoiceProducts);

        $productTotalHT = $product->getPriceHT() * $quantity;
        $productTotalTaxes = $product->getTaxRate() * $quantity;
        $productTotalTTC = $product->getPrice() * $quantity;

        $totalHT += $productTotalHT;
        $totalTaxes += $productTotalTaxes;
        $totalTTC += $productTotalTTC;

        return true;
    }

    private function updateOrCreateInvoiceProduct(Invoice $invoice, Product $product, int $quantity, ?array $existingInvoiceProducts): InvoiceProduct
    {
        if (isset($existingInvoiceProducts[$product->getId()])) {
            // Si le InvoiceProduct existe déjà, on met à jour la quantité.
            $invoiceProduct = $existingInvoiceProducts[$product->getId()];
            $invoiceProduct->setQuantity($quantity);
        } else {
            // Si le InvoiceProduct n'existe pas, on en crée un nouveau.
            $invoiceProduct = new InvoiceProduct();
            $invoiceProduct->setInvoice($invoice);
            $invoiceProduct->setProduct($product);
            $invoiceProduct->setQuantity($quantity);
            $invoiceProduct->setPrice($product->getPrice());
            $invoiceProduct->setTaxRate($product->getTaxRate());

            // Ajout du nouveau InvoiceProduct au facture (Invoice).
            $invoice->addInvoiceProduct($invoiceProduct);
        }

        // Avant de retourner le InvoiceProduct, on s'assure que toutes les propriétés non-null sont définies.
        if ($invoiceProduct->getPrice() === null || $invoiceProduct->getTaxRate() === null) {
            throw new \Exception("Le prix et le taux de taxe sont requis pour le InvoiceProduct.");
        }

        return $invoiceProduct;
    }

    private function removeUnmatchedInvoiceProducts(Invoice $invoice, array $invoiceProductsData, array $existingInvoiceProducts): void
    {
        $idsProductFromPost = array_column($invoiceProductsData, 'product_id');
        foreach ($existingInvoiceProducts as $existingProductId => $existingInvoiceProduct) {
            if (!in_array($existingProductId, $idsProductFromPost)) {
                $invoice->removeInvoiceProduct($existingInvoiceProduct);
                $this->entityManager->remove($existingInvoiceProduct);
            }
        }
    }

    public function create(Invoice $invoice, array $params): bool
    {
        $invoiceCustomerData = $this->decodeCustomerData($params);

        if (!$this->processCustomer($invoice, $invoiceCustomerData, null)) {
            return false;
        }

        $invoiceProductsData = $this->decodeInvoiceProductsData($params);

        if (!$invoiceProductsData) {
            $this->addError("La facture ne contient aucun produit.");
            return false;
        }

        if (!$this->processInvoiceProducts($invoice, $invoiceProductsData, null)) {
            return false;
        }

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return true;
    }

    public function exportPDF(Invoice $invoice): Response {
        $dompdf = $this->preparePDF($invoice);

        $filename = "facture-" . $invoice->getId() . ".pdf";
        $dompdf->stream($filename, [
            "Attachment" => true
        ]);

        return new Response('', 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function generatePDF(Invoice $invoice): string {
        $dompdf = $this->preparePDF($invoice);

        $output = $dompdf->output();
        $filename = sys_get_temp_dir() . "/facture-" . $invoice->getId() . ".pdf";
        file_put_contents($filename, $output);

        return $filename;
    }

    private function preparePDF(Invoice $invoice): \Dompdf\Dompdf
    {
        $html = $this->twig->render('back/invoice/export.html.twig', [
            'invoice' => $invoice
        ]);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        $dompdf = new \Dompdf\Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }
}
