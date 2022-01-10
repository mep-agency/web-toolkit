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
use Mep\MepWebToolkitK8sCli\Contract\AbstractK8sCommand;
use Mep\MepWebToolkitK8sCli\K8sCli;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[AsCommand(
    name: 'namespace:create',
    description: 'Creates a new namespace for deploying apps',
)]
class NamespaceCreateCommand extends AbstractK8sCommand
{
    protected function configure(): void
    {
        $this->addArgument(
            Argument::GENERIC_NAME,
            InputArgument::OPTIONAL,
            'The namespace name',
            K8sCli::K8S_DEFAULT_NAMESPACE,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        /** @var string $namespaceName */
        $namespaceName = $input->getArgument(Argument::GENERIC_NAME);

        $this->kubernetesCluster
            ->namespace()
            ->setName($namespaceName)
            ->setLabels(K8sCli::K8S_MINIMUM_NEW_RESOURCE_LABELS)
            ->create()
        ;

        $symfonyStyle->success('Namespace "'.$namespaceName.'" created successfully!');

        return Command::SUCCESS;
    }
}
