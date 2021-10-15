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
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
#[AsCommand(
    name: 'namespace:create',
    description: 'Creates a new namespace for deploying apps',
)]
class NamespaceCreateCommand extends AbstractK8sCommand
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::OPTIONAL, 'The namespace name', K8sCli::K8S_DEFAULT_NAMESPACE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $namespaceName = $input->getArgument('name');

        try {
            $this->kubernetesCluster
                ->namespace()
                ->setName($namespaceName)
                ->setLabels(K8sCli::K8S_MINIMUM_NEW_RESOURCE_LABELS)
                ->create()
            ;
        } catch (KubernetesAPIException $kubernetesapiException) {
            $symfonyStyle->error(
                'Failed creating namespace "'.$namespaceName.'": '.$kubernetesapiException->getPayload()['message'].'.',
            );

            return Command::FAILURE;
        }

        $symfonyStyle->success('Namespace "'.$namespaceName.'" created successfully!');

        return Command::SUCCESS;
    }
}
