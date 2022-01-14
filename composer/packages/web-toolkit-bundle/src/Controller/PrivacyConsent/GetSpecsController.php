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
use Mep\WebToolkitBundle\Service\PrivacyConsentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class GetSpecsController extends AbstractController
{
    #[Route('/specs/', name: RouteName::PRIVACY_CONSENT_GET_SPECS, methods: [Request::METHOD_GET])]
    public function __invoke(PrivacyConsentManager $privacyConsentManager): Response
    {
        $specsArray = [
            PrivacyConsentManager::JSON_KEY_SPECS => $privacyConsentManager->getSpecs(),
        ];

        return $this->json($specsArray);
    }
}
