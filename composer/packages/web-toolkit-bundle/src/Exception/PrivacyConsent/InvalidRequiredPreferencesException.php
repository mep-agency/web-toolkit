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

use Mep\WebToolkitBundle\Contract\Exception\AbstractPrivacyConsentException;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
final class InvalidRequiredPreferencesException extends AbstractPrivacyConsentException
{
    public function __construct(string $service)
    {
        parent::__construct(sprintf('Required "%s" service preference was not given.', $service));
    }
}
