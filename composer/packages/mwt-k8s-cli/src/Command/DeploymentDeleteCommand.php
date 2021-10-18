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
    name: 'deployment:delete',
    description: 'Deletes an app deployment',
)]
class DeploymentDeleteCommand extends AbstractHelmCommand
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The deployment name');

        $this->addOption(
            'app-env',
            null,
            InputOption::VALUE_REQUIRED,
            'Runs this command just on a specific env deployment (e.g. "staging")',
        );
        $this->addOption(
            'namespace',
            null,
            InputOption::VALUE_REQUIRED,
            'The namespace associated to the deployment',
            K8sCli::K8S_DEFAULT_NAMESPACE,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $deploymentName = $input->getArgument('name');
        $namespace = $input->getOption('namespace');
        $appEnv = $input->getOption('app-env');

        if (! $this->helmDeploymentsManager->delete($deploymentName, $appEnv, $namespace, $symfonyStyle)) {
            return Command::FAILURE;
        }

        $symfonyStyle->success('Deployment "'.$deploymentName.'" deleted successfully!');

        return Command::SUCCESS;
    }
}
