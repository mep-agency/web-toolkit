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

namespace Mep\MonorepoUtils\ReleaseWorker;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class TemporaryReleaseWorker implements ReleaseWorkerInterface
{
    /**
     * @param Version $version
     */
    public function getDescription($version): string
    {
        return 'Release command is temporary disabled until full implementation is provided';
    }

    /**
     * @param Version $version
     */
    public function work($version): void
    {
        // Nothing to do here...
    }
}
