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
use Mep\MwtK8sCli\Exception\StopExecutionException;
use Mep\MwtK8sCli\K8sCli;
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
    name: 'super-user:delete',
    description: 'Deletes a super-user service account from the given namespace.',
)]
class SuperUserDeleteCommand extends AbstractK8sCommand
{
    protected function configure(): void
    {
        $this->addArgument('service-account', InputArgument::REQUIRED, 'The service account name');

        $this->addOption(
            'namespace',
            null,
            InputOption::VALUE_REQUIRED,
            'The namespace associated to the service account',
            K8sCli::K8S_DEFAULT_NAMESPACE,
        );
        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Deletes the service account even if it was not created by this CLI',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $serviceAccountName = $input->getArgument('service-account');
        $namespace = $input->getOption('namespace');
        $deletedResourcesCounter = 0;

        // Delete role binding...
        try {
            $this->deleteOrStop(
                $this->kubernetesCluster
                    ->getRoleBindingByName($serviceAccountName.'-role-binding', $namespace),
                $input,
                $output,
            );

            ++$deletedResourcesCounter;
        } catch (StopExecutionException $stopExecutionException) {
            // Just skip this...
        } catch (KubernetesAPIException $kubernetesapiException) {
            $symfonyStyle->warning(
                'Failed deleting the role binding: '.($kubernetesapiException->getPayload()['message'] ?? 'no error message').'.',
            );
        }

        // Delete role...
        try {
            $this->deleteOrStop(
                $this->kubernetesCluster
                    ->getRoleByName($serviceAccountName.'-role', $namespace),
                $input,
                $output,
            );

            ++$deletedResourcesCounter;
        } catch (StopExecutionException $stopExecutionException) {
            // Just skip this...
        } catch (KubernetesAPIException $stopExecutionException) {
            $symfonyStyle->warning(
                'Failed deletig the role: '.($stopExecutionException->getPayload()['message'] ?? 'no error message').'.',
            );
        }

        // Delete service account...
        try {
            $this->deleteOrStop(
                $this->kubernetesCluster
                    ->getServiceAccountByName($serviceAccountName, $namespace),
                $input,
                $output,
            );

            ++$deletedResourcesCounter;
        } catch (StopExecutionException $stopExecutionException) {
            // Just skip this...
        } catch (KubernetesAPIException $stopExecutionException) {
            $symfonyStyle->warning(
                'Failed deleting the service account: '.($stopExecutionException->getPayload()['message'] ?? 'no error message').'.',
            );
        }

        // Check result...
        if ($deletedResourcesCounter < 1) {
            $symfonyStyle->warning('No resource has been deleted.');

            return Command::INVALID;
        }

        if ($deletedResourcesCounter < 3) {
            $symfonyStyle->error(
                'Only '.$deletedResourcesCounter.' resource'.(1 === $deletedResourcesCounter ? '' : 's').' (out of 3) '.(1 === $deletedResourcesCounter ? 'has' : 'have').' been deleted successfully.',
            );

            return Command::INVALID;
        }

        $symfonyStyle->success('Service account "'.$serviceAccountName.'" deleted successfully!');

        return Command::SUCCESS;
    }
}
