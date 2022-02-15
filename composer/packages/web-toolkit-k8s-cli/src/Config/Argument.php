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
class Argument
{
    /**
     * @var string
     */
    public final const NAMESPACE = Option::NAMESPACE;

    /**
     * @var string
     */
    public final const GENERIC_NAME = 'name';

    /**
     * @var string
     */
    public final const SERVICE_ACCOUNT = 'service-account';

    /**
     * @var string
     */
    public final const APP_NAME = 'app-name';

    /**
     * @var string
     */
    public final const ENVIRONMENT = 'environment';
}
