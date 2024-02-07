<?php namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName($faker->word);
            $product->setDescription($faker->sentence);
            $product->setPrice($faker->randomFloat(2, 10, 500));
            $product->setTaxRate($faker->randomFloat(2, 5, 25));
            $product->setStripeProductId($faker->uuid);
            $product->setStripePriceId($faker->uuid);

            $manager->persist($product);
            $this->addReference('product-' . $i, $product);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        // L'ordre dans lequel cette fixture sera exécutée
        return 1;
    }
}
