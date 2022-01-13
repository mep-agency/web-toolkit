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

namespace Mep\WebToolkitBundle\Config;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class RouteName
{
    /**
     * @var string
     */
    public const LOGIN = 'login';

    /**
     * @var string
     */
    public const PRIVACY_CONSENT_CREATE = 'privacy_consent_create';

    /**
     * @var string
     */
    public const PRIVACY_CONSENT_UPDATE = 'privacy_consent_update';

    /**
     * @var string
     */
    public const PRIVACY_CONSENT_GET_SPECS = 'privacy_consent_get_specs';

    /**
     * @var string
     */
    public const PRIVACY_CONSENT_GET = 'privacy_consent_get';

    /**
     * @var string
     */
    public const PRIVACY_CONSENT_GET_HISTORY = 'privacy_consent_get_history';
}
