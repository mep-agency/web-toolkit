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
use Mep\MepWebToolkitK8sCli\Service\K8sConfigGenerator;
use RuntimeException;
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
    name: 'config:create',
    description: 'Creates a new local kubectl config file',
)]
class ConfigCreateCommand extends Command
{
    public function __construct(
        private K8sConfigGenerator $k8sConfigGenerator,
        private string $kubeConfigPath,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            Option::CERTIFICATE,
            'c',
            InputArgument::OPTIONAL,
            'Path to the CA certificate file',
            './ca.crt',
        );
        $this->addOption(Option::FORCE, null, InputOption::VALUE_NONE, 'Overwrite existing config');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $force = $input->getOption(Option::FORCE) ?? false;
        $certificatePath = $input->getOption(Option::CERTIFICATE);

        if (! $input->isInteractive()) {
            $symfonyStyle->error('This command cannot run in "--no-interaction" mode.');

            return Command::INVALID;
        }

        if (! $force && is_file($this->kubeConfigPath)) {
            $symfonyStyle->error('A configuration file already exists, please use "--force" to overwrite it.');

            return Command::INVALID;
        }

        if (! is_file($certificatePath)) {
            $symfonyStyle->error(
                'No certificate found at "'.$certificatePath.'", please use "--certificate" to specify a custom path.',
            );

            return Command::INVALID;
        }

        // Create a basic configuration file...
        $this->k8sConfigGenerator->generateConfigFileFromData(
            $this->kubeConfigPath,
            'default-user',
            base64_encode(file_get_contents($certificatePath) ?: ''),
            $symfonyStyle->ask('Cluster URL', null, function ($value) {
                if (null === $value) {
                    throw new RuntimeException('Cluster URL cannot be empty.');
                }

                return $value;
            }),
            $symfonyStyle->ask('Access token', null, function ($value) {
                if (null === $value) {
                    throw new RuntimeException('Access token cannot be empty.');
                }

                return $value;
            }),
        );

        $symfonyStyle->success('New configuration file created successfully!');

        return Command::SUCCESS;
    }
}
