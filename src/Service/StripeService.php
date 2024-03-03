<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private $stripeClient;
    private $entityManager;
    private $urlGenerator;

    public function __construct(string $stripeApiKey, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->stripeClient = new StripeClient($stripeApiKey);
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;

    }

    public function createStripeCustomer(Customer $customer): ?string
    {
        try {
            $stripeCustomer = $this->stripeClient->customers->create([
                'email' => $customer->getEmail(),
                'name' => $customer->getFirstName() . ' ' . $customer->getLastName(),
            ]);

            return $stripeCustomer->id;
        } catch (ApiErrorException $e) {
            return null;
        }
    }

    /**
     * @throws ApiErrorException
     * @throws \Exception
     */
    public function createPaymentSession(int $paymentId): string
    {
        $payment = $this->entityManager->getRepository(Payment::class)->find($paymentId);
        if (!$payment) {
            throw new \Exception('Paiement non trouvé.');
        }

        $paymentMethodType = $this->determineStripePaymentMethodType($payment);
        $invoice = $payment->getInvoice();
        $amount = $payment->getAmount();

        $session = $this->stripeClient->checkout->sessions->create([
            'payment_method_types' => [$paymentMethodType],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Paiement pour Facture #' . $invoice->getId(),
                    ],
                    'unit_amount' => $amount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->urlGenerator->generate('payment_success', ['paymentId' => $paymentId], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->urlGenerator->generate('payment_failed', ['paymentId' => $paymentId], UrlGeneratorInterface::ABSOLUTE_URL),
            'metadata' => ['payment_id' => $paymentId],
        ]);

        return $session->url;
    }

    private function determineStripePaymentMethodType(Payment $payment): string
    {
        $typeMapping = [
            'Carte de crédit' => 'card',
            'PayPal' => 'paypal',
            'Virement bancaire' => 'sepa_debit',
        ];

        $paymentMethodName = $payment->getPaymentMethod()->getName();

        return $typeMapping[$paymentMethodName] ?? 'card';
    }

    public function deleteStripeCustomer(Customer $customer): bool
    {
        if (empty($customer->getStripeCustomerId())) {
            return false;
        }

        try {
            $this->stripeClient->customers->delete($customer->getStripeCustomerId());
            return true;
        } catch (ApiErrorException $e) {
            return false;
        }
    }

    public function updateStripeCustomer(Customer $customer): bool
    {
        if (empty($customer->getStripeCustomerId())) {
            return false;
        }

        try {
            $this->stripeClient->customers->update($customer->getStripeCustomerId(), [
                'email' => $customer->getEmail(),
                'name' => $customer->getFirstName() . ' ' . $customer->getLastName(),
            ]);
            return true;
        } catch (ApiErrorException $e) {
            return false;
        }
    }

    public function deleteStripeProduct($stripeProductId)
    {
        if (!$stripeProductId) {
            return;
        }

        try {

            $this->stripeClient->products->update($stripeProductId, ['active' => false]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('Erreur Stripe lors de la suppression du produit: ' . $e->getMessage());
        }
    }


    public function createStripeProduct(Product $product, $billingType = 'one_time'): array
    {
        try {
            $stripeProduct = $this->stripeClient->products->create([
                'name' => $product->getName(),
                'description' => $product->getDescription(),
            ]);

            if ($billingType == 'recurring') {
                $stripePrice = $this->stripeClient->prices->create([
                    'product' => $stripeProduct->id,
                    'unit_amount' => $product->getPrice() * 100,
                    'currency' => 'eur',
                    'recurring' => ['interval' => 'month'],
                    'billing_scheme' => 'per_unit',
                ]);
            } else {
                $stripePrice = $this->stripeClient->prices->create([
                    'product' => $stripeProduct->id,
                    'unit_amount' => $product->getPrice() * 100,
                    'currency' => 'eur',
                ]);
            }

            return [
                'stripeProductId' => $stripeProduct->id,
                'stripePriceId' => $stripePrice->id,
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return [
                'stripeProductId' => null,
                'stripePriceId' => null,
            ];
        }
    }


    public function updateStripeProduct(Product $product, $newBillingType = null)
    {
        try {
            if ($product->getStripeProductId()) {
                $stripeProduct = $this->stripeClient->products->update(
                    $product->getStripeProductId(),
                    [
                        'name' => $product->getName(),
                        'description' => $product->getDescription(),
                    ]
                );
            } else {
                return [];
            }

            $stripePriceId = null;
            if ($newBillingType) {
                $priceData = [
                    'product' => $stripeProduct->id,
                    'unit_amount' => $product->getPrice() * 100,
                    'currency' => 'eur',
                ];

                if ($newBillingType === 'recurring') {
                    $priceData['recurring'] = ['interval' => 'month'];
                }

                $stripePrice = $this->stripeClient->prices->create($priceData);
                $stripePriceId = $stripePrice->id;
            }

            return [
                'stripeProductId' => $stripeProduct->id,
                'stripePriceId' => $stripePriceId,
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('Erreur Stripe lors de la mise à jour du produit: ' . $e->getMessage());
            return [];
        }
    }



}
