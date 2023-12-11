<?php namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\InvoiceStatus;

class InvoiceStatusFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $statusTitles = ['Payée', 'Impayée', 'Annulée'];
        
        foreach ($statusTitles as $title) {
            $status = new InvoiceStatus();
            $status->setTitle($title);
            $manager->persist($status);
        }

        $manager->flush();
    }
}
