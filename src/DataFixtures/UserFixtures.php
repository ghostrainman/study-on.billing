<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const USERS = [
        [
            'email' => 'user@example.com',
            'roles' => ['ROLE_USER'],
            'pass' => '123456',
            'balance' => '1000'
        ],
        [
            'email' => 'admin@example.com',
            'roles' => ['ROLE_SUPER_ADMIN'],
            'pass' => '123456',
            'balance' => '5000'
        ]
    ];
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::USERS as $user) {
            $newUser = new User();
            $newUser->setEmail($user['email']);
            $newUser->setRoles($user['roles']);
            $newUser->setPassword($this->passwordHasher->hashPassword($newUser, $user['pass']));
            $newUser->setBalance($user['balance']);
            $manager->persist($newUser);
        }

        $manager->flush();
    }
}
