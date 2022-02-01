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

namespace Mep\WebToolkitBundle\Exception\PrivacyConsent;

use Exception;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class InvalidUserConsentDataException extends Exception
{
    /**
     * @var string
     */
    public const CANNOT_UPDATE_CONSENT_FOR_UNEXISTING_PUBLIC_KEY = 'cannot_update_consent_for_unexisting_public_key';

    /**
     * @var string
     */
    private const TRANSLATION_DOMAIN = 'invalid-user-consent-data';

    public function __construct(string $reasonTranslationKey)
    {
        parent::__construct($reasonTranslationKey);
    }

    public function getTranslatedMessage(TranslatorInterface $translator): string
    {
        return $translator->trans($this->getMessage(), [], self::TRANSLATION_DOMAIN);
    }
}
