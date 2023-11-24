<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    private Generator $faker;

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        for ($i=1; $i <= 75; $i++) {

            $email = null;
            $username = null;
            $firstname = null;
            $lastname = null;

            while (!$username) {
                $tempFirstname = $this->faker->firstName();
                $tempLastname = $this->faker->lastName();
                $tempEmail = strtolower(str_replace(' ', '', $tempFirstname . '.' . $tempLastname . '@gmail.com'));
                $tempEmail = preg_replace('/&([A-Za-z]{1,2})(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);/', '$1', htmlentities($tempEmail));
                $tempUsename = strtolower(str_replace(' ', '', $tempFirstname . $tempLastname));

                try {
                    $this->getReference("user_$tempUsename");
                    $username = null;
                } catch(\Exception $e) {
                    $username = $tempUsename;
                    $firstname = $tempFirstname;
                    $lastname = $tempLastname;
                    $email = $tempEmail;
                }
            }

            $isAdmin = $i === 1 ? true : false;
            

            $user = (new User())
                ->setEmail($email)
                ->setUsername($username)
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setRoles($isAdmin ? ['ROLE_ADMIN'] : []);
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'root123456');
            $user->setPassword($hashedPassword);
            $manager->persist($user); 

            $this->addReference("user_$i", $user);
            $this->addReference("user_$username", $user);
        }
        $manager->flush();
    }
}
