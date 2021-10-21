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

namespace Mep\MwtK8sCli\Command;

use Mep\MwtK8sCli\Config\Argument;
use Mep\MwtK8sCli\Config\Option;
use Mep\MwtK8sCli\Contract\AbstractHelmCommand;
use Mep\MwtK8sCli\K8sCli;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
#[AsCommand(
    name: 'app:install',
    description: 'Installs an app using Helm',
)]
class AppInstallCommand extends AbstractHelmCommand
{
    protected function configure(): void
    {
        $this->addArgument(Argument::GENERIC_NAME, InputArgument::REQUIRED, 'The app name');

        $this->addOption(
            Option::ENV,
            null,
            InputOption::VALUE_REQUIRED,
            'Runs this command just on a specific env (e.g. "staging")',
        );
        $this->addOption(
            Option::NAMESPACE,
            null,
            InputOption::VALUE_REQUIRED,
            'The namespace to associate with the new app',
            K8sCli::K8S_DEFAULT_NAMESPACE,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $appName = $input->getArgument(Argument::GENERIC_NAME);
        $namespace = $input->getOption(Option::NAMESPACE);
        $appEnv = $input->getOption(Option::ENV);

        if (! $this->helmAppsManager->install($appName, $appEnv, $namespace, $symfonyStyle)) {
            return Command::FAILURE;
        }

        $symfonyStyle->success('App "'.$appName.'" installed successfully!');

        return Command::SUCCESS;
    }
}
