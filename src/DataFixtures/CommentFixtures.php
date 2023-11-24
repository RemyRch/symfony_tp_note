<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;


class CommentFixtures extends Fixture implements DependentFixtureInterface
{

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        for ($i=1; $i <= 500; $i++) {
            $comment = (new Comment())
                ->setContent($this->faker->paragraph(3))
                ->setAuthor($this->getReference("user_" . $this->faker->numberBetween(2, 75)))
                ->setEvent($this->getReference("event_" . $this->faker->numberBetween(1, 200)));
            $manager->persist($comment); 

            $this->addReference("comment_$i", $comment);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            EventFixtures::class,
        ];
    }
}
