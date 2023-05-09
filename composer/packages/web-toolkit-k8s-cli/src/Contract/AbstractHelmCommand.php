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

namespace Mep\MepWebToolkitK8sCli\Contract;

use LogicException;
use Mep\MepWebToolkitK8sCli\Config\Argument;
use Mep\MepWebToolkitK8sCli\Config\Option;
use Mep\MepWebToolkitK8sCli\Exception\StopExecutionException;
use Mep\MepWebToolkitK8sCli\K8sCli;
use Mep\MepWebToolkitK8sCli\Service\HelmAppsManager;
use RenokiCo\PhpK8s\KubernetesCluster;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
abstract class AbstractHelmCommand extends AbstractK8sCommand
{
    public function __construct(
        KubernetesCluster $kubernetesCluster,
        protected HelmAppsManager $helmAppsManager,
        private readonly bool $supportsAllEnvironmentsFlag = true,
    ) {
        parent::__construct($kubernetesCluster);

        $this->addArgument(Argument::APP_NAME, InputArgument::REQUIRED, 'The app name');
        $this->addArgument(
            Argument::ENVIRONMENT,
            InputArgument::OPTIONAL,
            'The environment to run the Helm commands on (e.g. "staging")',
        );

        if ($this->supportsAllEnvironmentsFlag) {
            $this->addOption(
                Option::ALL_ENVIRONMENTS,
                'a',
                InputOption::VALUE_NONE,
                'Runs this command on all the environments',
            );
        }

        $this->addOption(
            Option::NAMESPACE,
            null,
            InputOption::VALUE_REQUIRED,
            'The namespace associated to the app',
            K8sCli::K8S_DEFAULT_NAMESPACE,
        );
    }

    public function getAppName(InputInterface $input): string
    {
        $appName = $input->getArgument(Argument::APP_NAME);

        if (! is_string($appName)) {
            throw new LogicException('Data is not of the correct type.');
        }

        return $appName;
    }

    public function getAppEnvironment(InputInterface $input, OutputInterface $output): ?string
    {
        /** @var ?string $environment */
        $environment = $input->getArgument(Argument::ENVIRONMENT);
        $allEnvironments = $this->supportsAllEnvironmentsFlag && $input->getOption(Option::ALL_ENVIRONMENTS);

        if (empty($environment) && ! $allEnvironments) {
            throw new StopExecutionException(
                $this->supportsAllEnvironmentsFlag ? 'No environment specified, please use "--'.Option::ALL_ENVIRONMENTS.'" to run this command on all environments' : 'Missing required argument: '.Argument::ENVIRONMENT,
                Command::INVALID,
            );
        }

        if ($allEnvironments && ! empty($environment)) {
            throw new StopExecutionException(
                'You cannot use "--'.Option::ALL_ENVIRONMENTS.'" and specify a single environment at the same time',
                Command::INVALID,
            );
        }

        return $environment;
    }

    public function getNamespace(InputInterface $input): string
    {
        $namespace = $input->getOption(Option::NAMESPACE);

        if (! is_string($namespace)) {
            throw new LogicException('Data is not of the correct type.');
        }

        return $namespace;
    }
}
