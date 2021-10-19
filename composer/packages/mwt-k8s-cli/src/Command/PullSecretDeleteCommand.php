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

use Mep\MwtK8sCli\Argument;
use Mep\MwtK8sCli\Contract\AbstractK8sCommand;
use Mep\MwtK8sCli\K8sCli;
use Mep\MwtK8sCli\Option;
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
    name: 'pull-secret:delete',
    description: 'Deletes a Docker pull secret associated to the given namespace.',
)]
class PullSecretDeleteCommand extends AbstractK8sCommand
{
    public function __construct(
        KubernetesCluster $kubernetesCluster,
        private K8sPullSecretGenerator $k8sPullSecretGenerator,
    ) {
        parent::__construct($kubernetesCluster);
    }

    protected function configure(): void
    {
        $this->addArgument(Argument::GENERIC_NAME, InputArgument::REQUIRED, 'A name of the pull secret');

        $this->addOption(
            Option::NAMESPACE,
            null,
            InputOption::VALUE_REQUIRED,
            'The namespace associated the pull secret',
            K8sCli::K8S_DEFAULT_NAMESPACE,
        );
        $this->addOption(
            Option::FORCE,
            null,
            InputOption::VALUE_NONE,
            'Deletes the pull secret even if it was not created by this CLI',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $pullSecretName = $input->getArgument(Argument::GENERIC_NAME);
        $namespace = $input->getOption(Option::NAMESPACE);

        $k8sSecret = $this->kubernetesCluster->getSecretByName($pullSecretName, $namespace);

        $this->k8sPullSecretGenerator->isValidSecretOrStop($k8sSecret);
        $this->deleteOrStop($k8sSecret, $input, $output);

        $symfonyStyle->success('Pull secret  "'.$pullSecretName.'" deleted successfully!');

        return Command::SUCCESS;
    }
}
