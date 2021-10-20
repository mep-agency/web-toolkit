<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Config\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\LogicException;

class LoginCheckController extends AbstractController
{
    #[Route('/login-check', name: RouteName::LOGIN_CHECK)]
    public function __invoke(): void
    {
        throw new LogicException(
            'This method can be blank - it will be intercepted by the check_route key on your firewall.',
        );
    }
}
