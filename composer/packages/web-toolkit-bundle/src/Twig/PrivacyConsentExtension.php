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

namespace Mep\WebToolkitBundle\Twig;

use Mep\WebToolkitBundle\Config\RouteName;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class PrivacyConsentExtension extends AbstractExtension
{
    /**
     * @var string
     */
    private const ENDPOINT_GET_SPECS = 'getSpecs';

    /**
     * @var string
     */
    private const ENDPOINT_GET_HISTORY = 'getHistory';

    /**
     * @var string
     */
    private const ENDPOINT_CONSENT_GET = 'consentGet';

    /**
     * @var string
     */
    private const ENDPOINT_CONSENT_CREATE = 'consentCreate';

    /**
     * @var string
     */
    private const PUBLIC_KEY_HASH_PLACEHOLDER = '0000000000000000000000000000000000000000000000000000000000000000';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('privacy_consent_endpoints', function (): array {
                return $this->getPrivacyConsentEndpoint();
            }),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getPrivacyConsentEndpoint(): array
    {
        return [
            self::ENDPOINT_GET_SPECS => $this->urlGenerator->generate(
                RouteName::PRIVACY_CONSENT_GET_SPECS,
                [],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
            self::ENDPOINT_GET_HISTORY => $this->urlGenerator->generate(
                RouteName::PRIVACY_CONSENT_GET_HISTORY,
                [
                    'hash' => self::PUBLIC_KEY_HASH_PLACEHOLDER,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
            self::ENDPOINT_CONSENT_GET => $this->urlGenerator->generate(
                RouteName::PRIVACY_CONSENT_GET,
                [
                    'hash' => self::PUBLIC_KEY_HASH_PLACEHOLDER,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
            self::ENDPOINT_CONSENT_CREATE => $this->urlGenerator->generate(
                RouteName::PRIVACY_CONSENT_CREATE,
                [],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
        ];
    }
}
