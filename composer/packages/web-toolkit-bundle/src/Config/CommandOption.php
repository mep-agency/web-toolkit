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
 */
class CommandOption
{
    /**
     * @var string
     */
    final public const DRY_RUN = 'dry-run';

    /**
     * @var string
     */
    final public const IGNORE_MISSING_PDO_SESSION_HANDLER = 'ignore-missing-pdo-session-handler';
}
