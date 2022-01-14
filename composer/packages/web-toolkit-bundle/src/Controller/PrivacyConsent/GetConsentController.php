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
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PrivacyConsent;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class GetConsentController extends AbstractController
{
    #[Route('/{token<[0-9a-f]{8}-[0-9a-f]{4}-[04][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}>}/', name: RouteName::PRIVACY_CONSENT_GET, methods: [
        Request::METHOD_GET,
    ])]
    public function __invoke(string $token, PrivacyConsentRepository $privacyConsentRepository): Response
    {
        $token = Uuid::fromString($token);
        $privacyConsent = $privacyConsentRepository->findLastByToken($token);

        if (! $privacyConsent instanceof PrivacyConsent) {
            throw $this->createNotFoundException('Token not found.');
        }

        return $this->json($privacyConsent->getData());
    }
}
