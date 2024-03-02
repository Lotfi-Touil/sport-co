<?php namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\Invoice;
use App\Entity\InvoiceStatus;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class InvoiceFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        $status = $manager->getRepository(InvoiceStatus::class)->findOneBy(['title' => 'Impayée']);

        if (!$status) {
            throw new \LogicException('Statut de facture "Impayée" introuvable');
        }

        for ($i = 0; $i < 10; $i++) { 
            $invoice = new Invoice();
            $totalAmount = $faker->randomFloat(2, 100, 1000);

            $invoice->setTotalAmount($totalAmount);
            $invoice->setSubtotal($totalAmount - 20);
            $invoice->setCreatedAt(new \DateTime());
            $invoice->setUpdatedAt(new \DateTime());
            $invoice->setSubmittedAt($faker->dateTimeBetween('-6 months'));
            $invoice->setExpiryDate($faker->dateTimeBetween('-6 months'));
            $invoice->setInvoiceStatus($status);

            $manager->persist($invoice);
            $this->addReference('invoice-' . $i, $invoice);
        }
        
        $manager->flush();
    }

    public function getOrder()
    {
        // Assurez-vous que cette fixture est chargée après les dépendances nécessaires
        return 2;
    }

    public function getDependencies()
    {
        return [
            InvoiceStatusFixtures::class,
            CustomerFixtures::class,
        ];
    }
}
