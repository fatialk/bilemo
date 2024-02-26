<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Client;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientsFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
         // Création d'un admin
         $admin = new Client();
         $admin->setReference('admin1');
         $admin->setCompagnyName('Bilemo');
         $admin->setEmail("admin@bilemo.com");
         $admin->setRoles(["ROLE_ADMIN"]);
         $admin->setPassword($this->userPasswordHasher->hashPassword($admin, "password"));

         $manager->persist($admin);


        for ($i = 1; $i < 11; $i++) {
            $faker = Factory::create();
            $client = new Client();
            $client->setCompagnyName($faker->unique()->text(15));
            $client->setEmail($faker->unique()->email());
            $client->setRoles(["ROLE_USER"]);
            $client->setPassword($this->userPasswordHasher->hashPassword($client, 'toto'));

            $manager->persist($client);
            $this->addReference('client' . $i, $client);
        }

        $manager->flush();
    }
}
