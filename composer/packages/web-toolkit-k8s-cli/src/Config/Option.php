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

namespace Mep\MepWebToolkitK8sCli\Config;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class Option
{
    /**
     * @var string
     */
    final public const NAMESPACE = 'namespace';

    /**
     * @var string
     */
    final public const FORCE = 'force';

    /**
     * @var string
     */
    final public const ALL_ENVIRONMENTS = 'all-environments';

    /**
     * @var string
     */
    final public const CERTIFICATE = 'certificate';

    /**
     * @var string
     */
    final public const OUTPUT = 'output';
}
