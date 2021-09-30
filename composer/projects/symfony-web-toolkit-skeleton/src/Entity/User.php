<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mep\WebToolkitBundle\Contract\Entity\AbstractUser;

#[ORM\Entity]
class User extends AbstractUser
{
}
