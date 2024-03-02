<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Création d'administrateurs
        for ($i = 0; $i < 2; $i++) {
            $user = $this->createUser($faker->email, ['ROLE_ADMIN'], $manager, $faker);
        }

        // Création de propriétaires d'entreprise
        for ($i = 0; $i < 3; $i++) {
            $user = $this->createUser($faker->email, ['ROLE_COMPANY'], $manager, $faker);
        }

        // Création d'employés d'entreprise
        for ($i = 0; $i < 3; $i++) {
            $user = $this->createUser($faker->email, ['ROLE_COMPANY_USER'], $manager, $faker);
        }

        // Création d'utilisateurs génériques
        for ($i = 0; $i < 2; $i++) {
            $user = $this->createUser($faker->email, ['ROLE_USER'], $manager, $faker);
        }

        $manager->flush();
    }

    private function createUser($email, array $roles, ObjectManager $manager, $faker): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setFirstName($faker->firstName);
        $user->setLastName($faker->lastName);
        $user->setPhone($faker->phoneNumber);
        $user->setCreatedAt(new \DateTime());
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $manager->persist($user);

        return $user;
    }

}
