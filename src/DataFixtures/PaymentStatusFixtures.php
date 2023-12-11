<?php namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\PaymentStatus;

class PaymentStatusFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Créez quelques statuts de paiement
        $statusNames = ['Initial', 'En attente', 'Complété', 'Annulé'];

        foreach ($statusNames as $name) {
            $status = new PaymentStatus();
            $status->setName($name);
            $manager->persist($status);
        }

        $manager->flush();
    }
}
