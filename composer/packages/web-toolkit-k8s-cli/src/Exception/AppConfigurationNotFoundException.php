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

namespace Mep\MepWebToolkitK8sCli\Exception;

use RuntimeException;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class AppConfigurationNotFoundException extends RuntimeException
{
    public function __construct(string $app, ?string $appEnv, string $appPath)
    {
        parent::__construct('Cannot find app configuration for "'.$app.'" ('.($appEnv ?: 'all envs').') in "'.$appPath.'".');
    }
}
