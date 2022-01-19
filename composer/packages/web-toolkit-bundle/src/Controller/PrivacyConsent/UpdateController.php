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
use Mep\WebToolkitBundle\Contract\Exception\AbstractPrivacyConsentException;
use Mep\WebToolkitBundle\Service\PrivacyConsentManager;
use Nette\Utils\Json;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class UpdateController extends AbstractMwtController
{
    public function __construct(
        private PrivacyConsentManager $privacyConsentManager,
        private RequestStack $requestStack,
        ?SerializerInterface $serializer = null,
    ) {
        parent::__construct($serializer);
    }

    public function __invoke(string $token): Response
    {
        $token = Uuid::fromString($token);
        /** @var string $content */
        $content = $this->requestStack->getCurrentRequest()?->getContent();
        /** @var array<string, mixed> $contentArray */
        $contentArray = Json::decode($content, Json::FORCE_ARRAY);

        try {
            return $this->json([
                'token' => $this->privacyConsentManager->generateConsent($contentArray, $token,)->getData(),
            ]);
        } catch (AbstractPrivacyConsentException $abstractPrivacyConsentException) {
            return $this->json([
                'message' => $abstractPrivacyConsentException->getMessage(),
            ], 400);
        }
    }
}
