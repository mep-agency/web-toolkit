<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Mep\WebToolkitBundle\Contract\Controller\Security\AbstractLoginController;
use Mep\WebToolkitBundle\Contract\Entity\AbstractUser;
use Mep\WebToolkitBundle\Dto\LoginRequestProcessResultDto;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends AbstractLoginController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    protected function findUser(string $identifier): ?AbstractUser
    {
        return $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $identifier,
        ]);
    }

    protected function sendUrlToUser(LoginRequestProcessResultDto $loginRequestProcessResultDto): Response
    {
        return new Response($loginRequestProcessResultDto->loginLinkDetails->getUrl());
    }
}
