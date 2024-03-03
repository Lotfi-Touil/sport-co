<?php

namespace App\Service;

use App\Entity\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use App\Entity\Product;

class StripeService
{
    private $stripeClient;

    public function __construct(string $stripeApiKey)
    {
        $this->stripeClient = new StripeClient($stripeApiKey);
    }

    /**
     * @throws ApiErrorException
     */
    public function createStripeCustomer(Customer $customer): string
    {
        $stripeCustomer = $this->stripeClient->customers->create([
            'email' => $customer->getEmail(),
            'name' => $customer->getFirstName() . ' ' . $customer->getLastName(),
        ]);

        return $stripeCustomer->id;
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
