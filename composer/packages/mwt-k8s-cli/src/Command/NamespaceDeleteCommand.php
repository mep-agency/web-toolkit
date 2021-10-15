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
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
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
    name: 'namespace:delete',
    description: 'Deletes a the given namespace',
)]
class NamespaceDeleteCommand extends AbstractK8sCommand
{
    protected function configure(): void
    {
        $this->addArgument('namespace', InputArgument::REQUIRED, 'Name of the new namespace');

        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Delete the namespace even if it was not created by this CLI',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $namespaceName = $input->getArgument('namespace');

        try {
            $k8sNamespace = $this->kubernetesCluster
                ->getNamespaceByName($namespaceName)
            ;

            if (! $k8sNamespace->isActive()) {
                $symfonyStyle->warning(
                    'Namespace "'.$namespaceName.'" is not in "Active" state, please try again later...',
                );

                return Command::INVALID;
            }

            if ($input->isInteractive() && ! $symfonyStyle->confirm(
                'Deleting the namespace "'.$namespaceName.'" will delete all the associated resources and can\'t be undone. Are you sure?',
                false,
            )) {
                $symfonyStyle->info('Stopping execution...');

                return Command::SUCCESS;
            }

            $k8sNamespace->delete();

            $symfonyStyle->success('Namespace "'.$namespaceName.'" deleted successfully!');
        } catch (KubernetesAPIException $kubernetesapiException) {
            $symfonyStyle->error(
                'Failed deleting namespace "'.$namespaceName.'": '.$kubernetesapiException->getPayload()['message'].'.',
            );

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
