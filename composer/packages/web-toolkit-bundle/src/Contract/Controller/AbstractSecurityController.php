<?php

/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mep\WebToolkitBundle\Contract\Controller;

use Mep\WebToolkitBundle\Contract\Entity\AbstractUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
abstract class AbstractSecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(Request $request, LoginLinkHandlerInterface $loginLinkHandler): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $this->findUser($email);

            $lastUsername = $email;

            if ($user !== null) {
                $loginLinkDetails = $loginLinkHandler->createLoginLink($user);

                return $this->sendUrlToUser($user, $loginLinkDetails);
            } else {
                $error = new UserNotFoundException();
                $error->setUserIdentifier($email);
            }
        }

        return $this->render(
            '@EasyAdmin/page/login.html.twig',
            $this->configureLoginTemplateParameters([
                'last_username' => $lastUsername ?? null,
                'error' => $error ?? null,
                'csrf_token_intention' => 'authenticate',
            ]),
        );
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/login-check', name: 'login_check')]
    public function loginCheck(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    protected function configureLoginTemplateParameters(array $parameters): array
    {
        return $parameters;
    }

    abstract protected function findUser(string $identifier): ?AbstractUser;

    abstract protected function sendUrlToUser(AbstractUser $user, LoginLinkDetails $loginLinkDetails): Response;
}
