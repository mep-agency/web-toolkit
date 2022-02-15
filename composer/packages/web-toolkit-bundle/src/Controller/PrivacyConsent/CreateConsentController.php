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
use Mep\WebToolkitBundle\Exception\PrivacyConsent\InvalidUserConsentDataException;
use Mep\WebToolkitBundle\Service\PrivacyConsentManager;
use Nette\Utils\Json;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class CreateConsentController extends AbstractMwtController
{
    public function __construct(
        private readonly PrivacyConsentManager $privacyConsentManager,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        ?SerializerInterface $serializer = null,
    ) {
        parent::__construct($serializer);
    }

    public function __invoke(): Response
    {
        /** @var string $content */
        $content = $this->requestStack->getCurrentRequest()?->getContent();
        /** @var array<string, string> $contentArray */
        $contentArray = Json::decode($content, Json::FORCE_ARRAY);

        try {
            return $this->json($this->privacyConsentManager->generateConsent($contentArray));
        } catch (InvalidUserConsentDataException $invalidUserConsentDataException) {
            return $this->json([
                'code' => $invalidUserConsentDataException->getMessage(),
                'message' => $invalidUserConsentDataException->getTranslatedMessage($this->translator),
            ], 400);
        }
    }
}
