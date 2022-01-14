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

namespace Mep\WebToolkitBundle\Controller\PrivacyConsent;

use Mep\WebToolkitBundle\Config\RouteName;
use Mep\WebToolkitBundle\Contract\Exception\AbstractPrivacyConsentException;
use Mep\WebToolkitBundle\Service\PrivacyConsentManager;
use Nette\Utils\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class CreateController extends AbstractController
{
    #[Route('/', name: RouteName::PRIVACY_CONSENT_CREATE, methods: [Request::METHOD_POST])]
    public function __invoke(PrivacyConsentManager $privacyConsentManager, Request $request): Response
    {
        try {
            return $this->json([
                'token' => $privacyConsentManager->generateConsent(
                    Json::decode($request->getContent(), Json::FORCE_ARRAY),
                )->getToken(),
            ]);
        } catch (AbstractPrivacyConsentException $abstractPrivacyConsentException) {
            return $this->json([
                'message' => $abstractPrivacyConsentException->getMessage(),
            ], 400);
        }
    }
}
