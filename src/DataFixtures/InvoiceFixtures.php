<?php namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\Invoice;
use App\Entity\InvoiceStatus;

class InvoiceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        // Supposons que vous avez un statut "Impayée" dans votre base de données
        $status = $manager->getRepository(InvoiceStatus::class)->findOneBy(['title' => 'Impayée']);

        // S'assurer que le statut existe
        if (!$status) {
            throw new \LogicException('Statut de facture "Impayée" introuvable');
        }

        // Crée et remplit l'entité Invoice
        for ($i = 0; $i < 10; $i++) { 
            $invoice = new Invoice();
            $totalAmount = $faker->randomFloat(2, 100, 1000);

            $invoice->setTotalAmount($totalAmount);
            $invoice->setSubtotal($totalAmount - 20); 
            $invoice->setTaxes($totalAmount * 0.2);
            $invoice->setCreatedAt($faker->dateTimeBetween('-6 months'));
            $invoice->setUpdatedAt($faker->dateTimeBetween('-6 months'));
            $invoice->setSubmittedAt($faker->dateTimeBetween('-6 months'));
            $invoice->setExpiryDate($faker->dateTimeBetween('-6 months'));
            $invoice->setInvoiceStatus($status);
            $customerReference = 'customer_' . $i % 10;
            $customer = $this->getReference($customerReference);
            $invoice->setCustomer($customer);

            $manager->persist($invoice);
        }
        
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            InvoiceStatusFixtures::class,
            CustomerFixtures::class,
        ];
    }
}
