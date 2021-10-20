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

namespace Mep\MwtK8sCli\Config;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class Option
{
    /**
     * @var string
     */
    public const NAMESPACE = 'namespace';

    /**
     * @var string
     */
    public const FORCE = 'force';

    /**
     * @var string
     */
    public const ENV = 'env';

    /**
     * @var string
     */
    public const CERTIFICATE = 'certificate';

    /**
     * @var string
     */
    public const OUTPUT = 'output';
}
