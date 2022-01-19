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

use Mep\WebToolkitBundle\Contract\Controller\AbstractMwtController;
use Mep\WebToolkitBundle\Service\PrivacyConsentManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class GetSpecsController extends AbstractMwtController
{
    public function __construct(
        private PrivacyConsentManager $privacyConsentManager,
        ?SerializerInterface $serializer = null,
    ) {
        parent::__construct($serializer);
    }

    public function __invoke(): Response
    {
        $specsArray = [
            PrivacyConsentManager::JSON_KEY_SPECS => $this->privacyConsentManager->getSpecs(),
        ];

        return $this->json($specsArray);
    }
}
