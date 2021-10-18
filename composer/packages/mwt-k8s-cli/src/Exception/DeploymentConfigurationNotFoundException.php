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

namespace Mep\MwtK8sCli\Exception;

use RuntimeException;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class DeploymentConfigurationNotFoundException extends RuntimeException
{
    public function __construct(string $deployment, ?string $appEnv, string $deploymentPath)
    {
        parent::__construct('Cannot find deployment configuration for "'.$deployment.'" ('.($appEnv ?: 'all envs').') in "'.$deploymentPath.'".');
    }
}
