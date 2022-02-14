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

use Mep\MepWebToolkitK8sCli\Config\Argument;
use Mep\MepWebToolkitK8sCli\Config\Option;
use Mep\MepWebToolkitK8sCli\Contract\AbstractK8sCommand;
use Mep\MepWebToolkitK8sCli\K8sCli;
use Mep\MepWebToolkitK8sCli\Service\K8sConfigGenerator;
use RenokiCo\PhpK8s\KubernetesCluster;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[AsCommand(
    name: self::NAME,
    description: self::DESCRIPTION,
)]
class SuperUserGetConfigCommand extends AbstractK8sCommand
{
    /**
     * @var string
     */
    final public const NAME = 'super-user:get-config';

    /**
     * @var string
     */
    final public const DESCRIPTION = 'Generates a config file for the given super-user service account.';

    public function __construct(
        KubernetesCluster $kubernetesCluster,
        private readonly K8sConfigGenerator $k8sConfigGenerator,
        private readonly string $defaultOutputPath,
    ) {
        parent::__construct($kubernetesCluster);
    }

    protected function configure(): void
    {
        $this->addArgument(Argument::SERVICE_ACCOUNT, InputArgument::REQUIRED, 'The service account name');

        $this->addOption(Option::OUTPUT, 'o', InputOption::VALUE_REQUIRED, 'An output file', $this->defaultOutputPath);
        $this->addOption(
            Option::NAMESPACE,
            null,
            InputOption::VALUE_REQUIRED,
            'The namespace to associate with the new service account',
            K8sCli::K8S_DEFAULT_NAMESPACE,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        /** @var string $serviceAccountName */
        $serviceAccountName = $input->getArgument(Argument::SERVICE_ACCOUNT);
        /** @var string $namespace */
        $namespace = $input->getOption(Option::NAMESPACE);
        /** @var string $outputPath */
        $outputPath = $input->getOption(Option::OUTPUT);

        $this->k8sConfigGenerator->generateConfigFile($outputPath, $serviceAccountName, $namespace);

        $symfonyStyle->success(
            'Super-user configuration file created successfully!'.PHP_EOL.PHP_EOL.'Output file: '.realpath($outputPath),
        );

        return Command::SUCCESS;
    }
}
