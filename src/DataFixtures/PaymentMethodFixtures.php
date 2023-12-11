<?php namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\PaymentMethod;

class PaymentMethodFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Créez quelques méthodes de paiement
        $methodNames = ['Carte de crédit', 'PayPal', 'Virement bancaire'];

        foreach ($methodNames as $name) {
            $method = new PaymentMethod();
            $method->setName($name);
            $manager->persist($method);
        }

        $manager->flush();
    }
}
