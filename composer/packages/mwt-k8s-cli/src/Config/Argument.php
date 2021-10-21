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
class Argument
{
    /**
     * @var string
     */
    public const NAMESPACE = Option::NAMESPACE;

    /**
     * @var string
     */
    public const GENERIC_NAME = 'name';

    /**
     * @var string
     */
    public const SERVICE_ACCOUNT = 'service-account';

    /**
     * @var string
     */
    public const APP = 'app';
}
