<?php 

namespace App\DataFixtures;

use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CompanyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $company = new Company();
        $company->setName($faker->company)
                ->setEmail($faker->companyEmail)
                ->setSiret($faker->numerify('##########'))
                ->setPhone($faker->phoneNumber)
                ->setWebsite($faker->url)
                ->setDescription($faker->text)
                ->setCreatedAt($faker->dateTime())
                ->setUpdatedAt($faker->dateTime());

        $manager->persist($company);

        $manager->flush();

        // Store reference for use in other fixtures
        $this->addReference('company', $company);
    }
}
