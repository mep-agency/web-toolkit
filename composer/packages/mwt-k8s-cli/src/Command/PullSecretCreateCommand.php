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

use Mep\MwtK8sCli\Contract\AbstractK8sCommand;
use Mep\MwtK8sCli\K8sCli;
use Mep\MwtK8sCli\Service\K8sPullSecretGenerator;
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
 */
#[AsCommand(
    name: 'pull-secret:create',
    description: 'Creates a Docker pull secret associated with the given namespace.',
)]
class PullSecretCreateCommand extends AbstractK8sCommand
{
    public function __construct(
        KubernetesCluster $kubernetesCluster,
        private K8sPullSecretGenerator $k8sPullSecretGenerator,
    ) {
        parent::__construct($kubernetesCluster);
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'A name for the pull secret');

        $this->addOption(
            'namespace',
            null,
            InputOption::VALUE_REQUIRED,
            'The namespace to associate with the new pull secret',
            K8sCli::K8S_DEFAULT_NAMESPACE,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $pullSecretName = $input->getArgument('name');
        $namespace = $input->getOption('namespace');

        $this->k8sPullSecretGenerator->generate(
            $pullSecretName,
            $symfonyStyle->ask('Registry (e.g. "ghcr.io")', 'https://index.docker.io/v1/'),
            $symfonyStyle->ask('Username', null, function ($value) {
                return $this->notNull($value);
            }),
            $symfonyStyle->ask('Password (or token)', null, function ($value) {
                return $this->notNull($value);
            }),
            $namespace,
        )->create();

        $symfonyStyle->success('Pull secret  "'.$pullSecretName.'" created successfully!');

        return Command::SUCCESS;
    }
}