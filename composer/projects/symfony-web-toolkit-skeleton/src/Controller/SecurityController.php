<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Mep\WebToolkitBundle\Contract\Controller\AbstractSecurityController;
use Mep\WebToolkitBundle\Contract\Entity\AbstractUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;

class SecurityController extends AbstractSecurityController
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    protected function configureLoginTemplateParameters(array $parameters): array
    {
        $parameters['page_title'] = 'Login';

        return $parameters;
    }

    protected function findUser(string $identifier): ?AbstractUser
    {
        return $this->userRepository->findOneBy([
            'email' => $identifier,
        ]);
    }

    protected function sendUrlToUser(AbstractUser $user, LoginLinkDetails $loginLinkDetails): Response
    {
        return new Response($loginLinkDetails->getUrl());
    }
}
