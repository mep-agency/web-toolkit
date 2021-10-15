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
use RenokiCo\PhpK8s\K8s;
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
    name: 'super-user:create',
    description: 'Creates a super-user service account for the given namespace.',
)]
class SuperUserCreateCommand extends AbstractK8sCommand
{
    protected function configure(): void
    {
        $this->addArgument('service-account', InputArgument::REQUIRED, 'The service account name');

        $this->addOption('namespace', null, InputOption::VALUE_REQUIRED, 'The namespace to associate with the new service account', K8sCli::K8S_DEFAULT_NAMESPACE);

        $this->setHelp("!!!!!!!!!!!!!!!\n!!! WARNING !!!\n!!!!!!!!!!!!!!!\n\nThis is just a shortcut to create access tokens with full access to the cluster, please consider managing accounts and permissions manually for improved security.");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $serviceAccountName = $input->getArgument('service-account');
        $namespace = $input->getOption('namespace');

        try {
            $this->kubernetesCluster
                ->serviceAccount()
                ->setName($serviceAccountName)
                ->setNamespace($namespace)
                ->setLabels(K8sCli::K8S_MINIMUM_NEW_RESOURCE_LABELS)
                ->create()
            ;

            $role = $this->kubernetesCluster
                ->role()
                ->setName($serviceAccountName.'-role')
                ->setNamespace($namespace)
                ->setLabels(K8sCli::K8S_MINIMUM_NEW_RESOURCE_LABELS)
                ->addRule(
                    K8s::rule()
                        ->addApiGroup('*')
                        ->addResource('*')
                        ->addVerb('*'),
                )
                ->create()
            ;

            $this->kubernetesCluster
                ->roleBinding()
                ->setName($serviceAccountName.'-role-binding')
                ->setNamespace($namespace)
                ->setLabels(K8sCli::K8S_MINIMUM_NEW_RESOURCE_LABELS)
                ->setRole($role, 'rbac.authorization.k8s.io')
                ->setSubjects([
                    K8s::subject()
                        ->setKind('ServiceAccount')
                        ->setName($serviceAccountName)
                        ->setNamespace($namespace)
                ])
                ->create()
            ;
        } catch (KubernetesAPIException $kubernetesapiException) {
            $symfonyStyle->error('Failed creating service account "'.$serviceAccountName.'": '.$kubernetesapiException->getPayload()['message'].'.');

            return Command::FAILURE;
        }

        $symfonyStyle->success('Service account "'.$serviceAccountName.'" created successfully!');

        return Command::SUCCESS;
    }
}
