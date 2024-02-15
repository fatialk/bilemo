<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Client;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ClientsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i < 11; $i++) {
            $faker = Factory::create();
            $client = new Client();
            $client->setReference($faker->unique()->uuid());
            $client->setCompagnyName($faker->unique()->text(15));
            $client->setEmail($faker->unique()->email());
            $client->setPassword($faker->unique()->password());
            $client->setPassword($faker->unique()->password());

            $manager->persist($client);
            $this->addReference('client' . $i, $client);
        }

        $manager->flush();
    }
}
