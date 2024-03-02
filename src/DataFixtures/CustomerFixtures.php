<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CustomerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $customer = new Customer();
            $customer->setFirstName($faker->firstName)
                     ->setLastName($faker->lastName)
                     ->setEmail($faker->email)
                     ->setPhone($faker->phoneNumber)
                     ->setCreatedAt(new \DateTime())
                     ->setCompany($this->getReference('company'));

            $manager->persist($customer);
            // Ajout de la référence pour chaque customer
            $this->addReference('customer_' . $i, $customer);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CompanyFixtures::class,
        ];
    }
}
