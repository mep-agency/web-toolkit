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
use Symfony\Component\Uid\Uuid;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class UpdateController extends AbstractController
{
    #[Route('/{token<[0-9a-f]{8}-[0-9a-f]{4}-[04][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}>}/', name: RouteName::PRIVACY_CONSENT_UPDATE, methods: [
        Request::METHOD_POST,
    ])]
    public function __invoke(
        string $token,
        PrivacyConsentManager $privacyConsentManager,
        Request $request,
    ): Response {
        $token = Uuid::fromString($token);
        /** @var string $content */
        $content = $request->getContent();
        /** @var array<string, mixed> $contentArray */
        $contentArray = Json::decode($content, Json::FORCE_ARRAY);

        try {
            return $this->json([
                'token' => $privacyConsentManager->generateConsent($contentArray, $token,)->getData(),
            ]);
        } catch (AbstractPrivacyConsentException $abstractPrivacyConsentException) {
            return $this->json([
                'message' => $abstractPrivacyConsentException->getMessage(),
            ], 400);
        }
    }
}
