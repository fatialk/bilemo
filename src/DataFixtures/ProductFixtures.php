<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i < 21; $i++) {
            $faker = Factory::create();
            $product = new Product();
            $product->setName($faker->text(10));
            $product->setImage($faker->imageUrl(640, 480, 'smartphone', true));
            $product->setDescription($faker->text(200));
            $manager->persist($product);
        }

        $manager->flush();
    }
}
