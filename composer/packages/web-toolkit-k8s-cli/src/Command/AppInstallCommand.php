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

namespace Mep\MepWebToolkitK8sCli\Command;

use Mep\MepWebToolkitK8sCli\Contract\AbstractHelmCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
#[AsCommand(name: self::NAME, description: self::DESCRIPTION)]
class AppInstallCommand extends AbstractHelmCommand
{
    /**
     * @var string
     */
    final public const NAME = 'app:install';

    /**
     * @var string
     */
    final public const DESCRIPTION = 'Installs an app using Helm';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $appName = $this->getAppName($input);
        $appEnvironment = $this->getAppEnvironment($input, $output);
        $namespace = $this->getNamespace($input);

        $this->helmAppsManager->install($appName, $appEnvironment, $namespace, $symfonyStyle);

        $symfonyStyle->success('App "'.$appName.'" installed successfully!');

        return Command::SUCCESS;
    }
}
