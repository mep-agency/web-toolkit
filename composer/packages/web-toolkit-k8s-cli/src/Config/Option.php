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
    public final const NAMESPACE = 'namespace';

    /**
     * @var string
     */
    public final const FORCE = 'force';

    /**
     * @var string
     */
    public final const ALL_ENVIRONMENTS = 'all-environments';

    /**
     * @var string
     */
    public final const CERTIFICATE = 'certificate';

    /**
     * @var string
     */
    public final const OUTPUT = 'output';
}
