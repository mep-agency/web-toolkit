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
use Mep\MwtK8sCli\Service\K8sBasicAuthSecretGenerator;
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
    name: 'basic-auth-secret:delete',
    description: 'Deletes a n HTTP Basic Auth secret associated to the given namespace.',
)]
class BasicAuthSecretDeleteCommand extends AbstractK8sCommand
{
    public function __construct(
        KubernetesCluster $kubernetesCluster,
        private K8sBasicAuthSecretGenerator $k8sBasicAuthSecretGenerator,
    ) {
        parent::__construct($kubernetesCluster);
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'A name of the HTTP Basic Auth secret');

        $this->addOption(
            'namespace',
            null,
            InputOption::VALUE_REQUIRED,
            'The namespace associated the HTTP Basic Auth secret',
            K8sCli::K8S_DEFAULT_NAMESPACE,
        );
        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Deletes the HTTP Basic Auth secret even if it was not created by this CLI',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $basicAuthSecretName = $input->getArgument('name');
        $namespace = $input->getOption('namespace');

        $k8sSecret = $this->kubernetesCluster->getSecretByName($basicAuthSecretName, $namespace);

        $this->k8sBasicAuthSecretGenerator->isValidSecretOrStop($k8sSecret);
        $this->deleteOrStop($k8sSecret, $input, $output);

        $symfonyStyle->success('HTTP Basic Auth secret  "'.$basicAuthSecretName.'" deleted successfully!');

        return Command::SUCCESS;
    }
}
