<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = ["Sports", "Musique", "Cinéma", "Balade", "Shopping"];

        foreach ($categories as $key => $category) {
            $category = (new Category())
                ->setTitle($category);
            $manager->persist($category);
            $this->addReference("category_$key", $category);
        }

        $manager->flush();
    }
}
