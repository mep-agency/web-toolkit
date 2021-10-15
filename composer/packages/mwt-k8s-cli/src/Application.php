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

namespace Mep\MwtK8sCli;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Traversable;

class Application extends SymfonyApplication
{
    /**
     * @param Traversable<Command> $commandsTraversable
     */
    public function __construct(Traversable $commandsTraversable)
    {
        parent::__construct('MEP Web Toolkit - K8s CLI');

        $this->addCommands(iterator_to_array($commandsTraversable));
    }
}
