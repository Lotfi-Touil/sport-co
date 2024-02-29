<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        // Création d'administrateurs
        for ($i = 0; $i < 2; $i++) {
            $user = $this->createUser($faker->email, ['ROLE_ADMIN'], $manager);
        }

        // Création de propriétaires d'entreprise
        for ($i = 0; $i < 3; $i++) {
            $user = $this->createUser($faker->email, ['ROLE_COMPANY'], $manager);
        }

        // Création d'employés d'entreprise
        for ($i = 0; $i < 3; $i++) {
            $user = $this->createUser($faker->email, ['ROLE_COMPANY_USER'], $manager);
        }

        // Création d'utilisateurs génériques
        for ($i = 0; $i < 2; $i++) {
            $user = $this->createUser($faker->email, ['ROLE_USER'], $manager);
        }

        $manager->flush();
    }

    private function createUser($email, array $roles, ObjectManager $manager): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $manager->persist($user);

        return $user;
    }
}
