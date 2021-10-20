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

namespace Mep\WebToolkitBundle\Contract\Controller\Security;

use Mep\WebToolkitBundle\Config\RouteName;
use Mep\WebToolkitBundle\Contract\Entity\AbstractUser;
use Mep\WebToolkitBundle\Dto\LoginRequestProcessResultDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
abstract class AbstractLoginController extends AbstractController implements AuthenticationEntryPointInterface
{
    /**
     * @var string
     */
    public const CSRF_TOKEN_INTENTION = 'mwt-authenticate';

    /**
     * @var string
     */
    public const LOGIN_TWIG_TEMPLATE = '@EasyAdmin/page/login.html.twig';

    #[Route('/login', name: RouteName::LOGIN)]
    public function __invoke(Request $request, LoginLinkHandlerInterface $loginLinkHandler): Response
    {
        $exception = null;

        try {
            $loginRequestProcessResultDto = $this->processLoginRequest($request, $loginLinkHandler);

            if (null !== $loginRequestProcessResultDto) {
                return $this->sendUrlToUser($loginRequestProcessResultDto);
            }
        } catch (UserNotFoundException $userNotFoundException) {
            $exception = $userNotFoundException;
        }

        return $this->render(
            static::LOGIN_TWIG_TEMPLATE,
            [
                'page_title' => 'Login',
                'error' => $exception,
                'csrf_token_intention' => static::CSRF_TOKEN_INTENTION,
            ],
        );
    }

    public function start(
        Request $request,
        AuthenticationException $authenticationException = null,
    ): RedirectResponse {
        return $this->redirectToRoute('login');
    }

    protected function processLoginRequest(
        Request $request,
        LoginLinkHandlerInterface $loginLinkHandler,
    ): ?LoginRequestProcessResultDto {
        if (! $request->isMethod('POST')) {
            return null;
        }

        $email = $request->request->get('email');
        $csrfToken = (string) $request->request->get('_csrf_token');

        if (! $this->isCsrfTokenValid(self::CSRF_TOKEN_INTENTION, $csrfToken)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->findUser((string) $email);

        if (null !== $user) {
            $loginLinkDetails = $loginLinkHandler->createLoginLink($user);

            return new LoginRequestProcessResultDto($user, $loginLinkDetails);
        }

        $userNotFoundException = new UserNotFoundException();
        $userNotFoundException->setUserIdentifier((string) $email);

        throw $userNotFoundException;
    }

    abstract protected function findUser(string $identifier): ?AbstractUser;

    abstract protected function sendUrlToUser(LoginRequestProcessResultDto $loginRequestProcessResultDto): Response;
}
