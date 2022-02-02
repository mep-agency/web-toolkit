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
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
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
    public const INVALID_SIGNATURE = 'invalid_signature';

    /**
     * @var string
     */
    public const INVALID_SPECS_HASH = 'invalid_specs_hash';

    /**
     * @var string
     */
    public const UNMATCHING_CONSENT_DATA_KEY = 'unmatching_consent_data_key';

    /**
     * @var string
     */
    public const INVALID_REQUIRED_PREFERENCES = 'invalid_required_preferences';

    /**
     * @var string
     */
    public const UNSET_TIMESTAMP = 'unset_timestamp';

    /**
     * @var string
     */
    public const TIMESTAMP_IS_NOT_INTEGER = 'timestamp_is_not_integer';

    /**
     * @var string
     */
    public const TIMESTAMP_IS_NOT_IN_VALID_RANGE = 'timestamp_is_not_in_valid_range';

    /**
     * @var string
     */
    public const TIMESTAMP_IS_PRIOR_TO_LATEST_CONSENT = 'timestamp_is_prior_to_latest_consent';

    /**
     * @var string
     */
    public const UNSET_USER_AGENT = 'unset_user_agent';

    /**
     * @var string
     */
    public const USER_AGENT_IS_NOT_STRING = 'user_agent_is_not_string';

    /**
     * @var string
     */
    public const INVALID_USER_AGENT = 'invalid_user_agent';

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