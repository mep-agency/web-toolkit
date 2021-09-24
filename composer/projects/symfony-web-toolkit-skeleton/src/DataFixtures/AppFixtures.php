<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $objectManager): void
    {
        $user = new User();

        $user->setEmail('dev@example.com');
        $user->setRoles(['ROLE_ADMIN']);

        $objectManager->persist($user);

        $objectManager->flush();
    }
}
