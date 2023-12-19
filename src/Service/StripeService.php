<?php

namespace App\Service;

use Stripe\StripeClient;
use App\Entity\Product;

class StripeService
{
    private $stripeClient;

    public function __construct(string $stripeApiKey)
    {
        $this->stripeClient = new StripeClient($stripeApiKey);
    }

    public function createStripeProduct(Product $product)
    {
        $stripeProduct = $this->stripeClient->products->create([
            'name' => $product->getName(),
            'description' => $product->getDescription(),
        ]);

        $stripePrice = $this->stripeClient->prices->create([
            'product' => $stripeProduct->id,
            'unit_amount' => $product->getPrice() * 100,
            'currency' => 'eur',
        ]);

        return [
            'stripeProductId' => $stripeProduct->id,
            'stripePriceId' => $stripePrice->id,
        ];
    }
}
