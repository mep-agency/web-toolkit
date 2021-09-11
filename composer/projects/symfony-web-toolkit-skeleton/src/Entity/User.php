<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Mep\WebToolkitBundle\Contract\Entity\AbstractUser;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User extends AbstractUser
{
}
