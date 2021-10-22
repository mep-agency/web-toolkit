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

use Mep\MepWebToolkitK8sCli\Config\Option;
use Mep\MepWebToolkitK8sCli\Contract\AbstractHelmCommand;
use Mep\MepWebToolkitK8sCli\Service\HelmAppsManager;
use Mep\MepWebToolkitK8sCli\Service\K8sConfigGenerator;
use RenokiCo\PhpK8s\KubernetesCluster;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
#[AsCommand(
    name: 'app:get-cd-service-account',
    description: 'Generates a config file for a service account associated with the given app (useful for continuous delivery).',
)]
class AppGetCdServiceAccountCommand extends AbstractHelmCommand
{
    public function __construct(
        KubernetesCluster $kubernetesCluster,
        HelmAppsManager $helmAppsManager,
        private K8sConfigGenerator $k8sConfigGenerator,
        private string $defaultOutputPath,
    ) {
        parent::__construct($kubernetesCluster, $helmAppsManager, false);
    }

    protected function configure(): void
    {
        $this->addOption(Option::OUTPUT, 'o', InputOption::VALUE_REQUIRED, 'An output file', $this->defaultOutputPath);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $appName = $this->getAppName($input);
        $appEnvironment = $this->getAppEnvironment($input, $output);
        $serviceAccountName = 'mwt-'.$appName.'-'.$appEnvironment.'-cd';
        $namespace = $this->getNamespace($input);
        $outputPath = $input->getOption(Option::OUTPUT);

        $this->k8sConfigGenerator->generateConfigFile($outputPath, $serviceAccountName, $namespace);

        $symfonyStyle->success(
            'Configuration file created successfully!'.PHP_EOL.PHP_EOL.'Output file: '.realpath($outputPath),
        );

        return Command::SUCCESS;
    }
}
