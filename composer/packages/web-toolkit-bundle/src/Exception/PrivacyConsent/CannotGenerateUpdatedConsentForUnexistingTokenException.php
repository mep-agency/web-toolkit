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

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class CannotGenerateUpdatedConsentForUnexistingTokenException extends Exception
{
    public function __construct()
    {
        parent::__construct("Cannot generate updated consent for unexisting token.");
    }
}
