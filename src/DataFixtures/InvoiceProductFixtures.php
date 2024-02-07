<?php namespace App\DataFixtures;

use App\Entity\InvoiceProduct;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class InvoiceProductFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 20; $i++) {
            
            if (!$this->hasReference('product-' . $i)) {
                throw new \RuntimeException("Reference to product-$i does not exist.");
            }
            $invoiceIndex = $i % 10;
            if (!$this->hasReference('invoice-' . $invoiceIndex)) {
                throw new \RuntimeException("Reference to invoice-" . $invoiceIndex . " does not exist.");
            }

            $invoiceProduct = new InvoiceProduct();
            $invoiceProduct->setProduct($this->getReference('product-' . $i));
            $invoiceProduct->setQuantity($faker->numberBetween(1, 10));
            $invoiceProduct->setPrice($faker->randomFloat(2, 10, 500));
            $invoiceProduct->setTaxRate($faker->randomFloat(2, 5, 25));
            $invoiceProduct->setCreatedAt($faker->dateTimeThisYear);
            $invoiceProduct->setUpdatedAt($faker->dateTimeThisYear);
            $invoiceProduct->setInvoice($this->getReference('invoice-' . $invoiceIndex));
            $manager->persist($invoiceProduct);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        // Cette fixture sera exécutée après ProductFixtures
        return 20;
    }
}
