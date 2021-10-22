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
use Mep\MepWebToolkitK8sCli\Contract\AbstractK8sCommand;
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
        $this->addArgument(Option::NAMESPACE, InputArgument::REQUIRED, 'The namespace name');

        $this->addOption(
            Option::FORCE,
            null,
            InputOption::VALUE_NONE,
            'Delete the namespace even if it was not created by this CLI',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $namespaceName = $input->getArgument(Option::NAMESPACE);

        $k8sNamespace = $this->kubernetesCluster
            ->getNamespaceByName($namespaceName)
        ;

        if (! $k8sNamespace->isActive()) {
            $symfonyStyle->warning(
                'Namespace "'.$namespaceName.'" is not in "Active" state, please try again later...',
            );

            return Command::INVALID;
        }

        $this->deleteOrStop($k8sNamespace, $input, $output);

        $symfonyStyle->success('Namespace "'.$namespaceName.'" deleted successfully!');

        return Command::SUCCESS;
    }
}
