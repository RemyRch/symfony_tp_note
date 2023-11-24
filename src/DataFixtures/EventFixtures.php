<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class EventFixtures extends Fixture implements DependentFixtureInterface
{

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        
        for ($i=1; $i <= 200; $i++) {

            $date = \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-6 months'));

            $startingAt = $date->setTime($this->faker->numberBetween(8, 20), 0, 0);
            $endingAt = $startingAt->add(new \DateInterval('PT' . $this->faker->numberBetween(1, 5) . 'H'));

            $event = (new Event())
                ->setTitle($this->faker->sentence(3))
                ->setDescription($this->faker->paragraph(3))
                ->setCity($this->faker->city())
                ->setAddress($this->faker->address())
                ->setPostalCode($this->faker->postcode())
                ->setStartingAt($startingAt)
                ->setEndingAt($endingAt)
                ->setCategory($this->getReference("category_" . $this->faker->numberBetween(0, 4)))
                ->setAuthor($this->getReference("user_" . $this->faker->numberBetween(2, 75)));

            $amountOfParticipant = $this->faker->numberBetween(0, 10);

            for ($j=0; $j < $amountOfParticipant; $j++) { 
                $event->addParticipant($this->getReference("user_" . $this->faker->numberBetween(2, 75)));
            }

            $manager->persist($event); 

            $this->addReference("event_$i", $event);

        }


        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
        ];
    }
}
