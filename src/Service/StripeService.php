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

    /**
     * @throws ApiErrorException
     */
    public function createStripeProduct(Product $product, $billingType = 'one_time'): array
    {
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
    }

}
