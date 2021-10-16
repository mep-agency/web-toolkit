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

namespace Mep\MwtK8sCli\Contract;

use Mep\MwtK8sCli\Exception\StopExecutionException;
use Mep\MwtK8sCli\Factory\KubernetesClusterFactory;
use Mep\MwtK8sCli\K8sCli;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\KubernetesCluster;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
abstract class AbstractK8sCommand extends Command
{
    public function __construct(
        protected KubernetesCluster $kubernetesCluster,
    ) {
        parent::__construct();
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        // Always run validation before execution
        $this->setCode(function (InputInterface $input, OutputInterface $output): int {
            if (! KubernetesClusterFactory::isConfiguredLocally()) {
                $symfonyStyle = new SymfonyStyle($input, $output);

                $symfonyStyle->error(
                    "This tool has loaded the kubectl configuration automatically, this may be invalid or risky.\n\nPlease create a local configuration file instead:\n> php bin/mwt-k8s config:create --help",
                );

                return Command::INVALID;
            }

            try {
                // Check given namespace names
                $namespaceNames = [];

                if ($input->hasArgument('namespace')) {
                    $namespaceNames[] = $input->getArgument('namespace');
                }

                if ($input->hasOption('namespace')) {
                    $namespaceNames[] = $input->getOption('namespace');
                }

                foreach ($namespaceNames as $namespaceName) {
                    $this->isCreatedByThisToolOrStop(
                        $this->kubernetesCluster->getNamespaceByName($namespaceName),
                        $input,
                        $output,
                    );
                }

                return $this->execute($input, $output);
            } catch (StopExecutionException $stopExecutionException) {
                $symfonyStyle = new SymfonyStyle($input, $output);

                if (! empty($stopExecutionException->getMessage())) {
                    $symfonyStyle->info($stopExecutionException->getMessage());
                }

                return $stopExecutionException->getCode();
            } catch (KubernetesAPIException $kubernetesapiException) {
                $symfonyStyle = new SymfonyStyle($input, $output);

                $symfonyStyle->error(
                    'K8s API error: '.($kubernetesapiException->getPayload()['message'] ?? 'no error message').'.',
                );

                return Command::FAILURE;
            }
        });

        return parent::run($input, $output);
    }

    /**
     * A simple callback for input validation.
     */
    public function notNull(mixed $value): mixed
    {
        if (null === $value) {
            throw new RuntimeException('Value cannot be empty.');
        }

        return $value;
    }

    protected function isCreatedByThisToolOrStop(
        K8sResource $k8sResource,
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $hasForce = $input->hasOption('force');
        $forceValue = $hasForce && $input->getOption('force');

        if ($forceValue || $this->isCreatedByThisTool($k8sResource)) {
            return;
        }

        $symfonyStyle->error(
            'The given '.$k8sResource->getKind().' ("'.$k8sResource->getName().'") was not created by this CLI'.
            ($hasForce ? ', use "--force" to skip this check.' : ''),
        );

        throw new StopExecutionException('', Command::FAILURE);
    }

    /**
     * @throws KubernetesAPIException
     */
    protected function deleteOrStop(K8sResource $k8sResource, InputInterface $input, OutputInterface $output): void
    {
        $this->isCreatedByThisToolOrStop($k8sResource, $input, $output);

        $symfonyStyle = new SymfonyStyle($input, $output);

        if ($input->isInteractive() && ! $symfonyStyle->confirm(
            'You are about to delete the '.$k8sResource->getKind().' "'.$k8sResource->getName().'", this can\'t be undone. Are you sure?',
            false,
        )) {
            throw new StopExecutionException();
        }

        $k8sResource->delete();
    }

    private function isCreatedByThisTool(K8sResource $k8sResource): bool
    {
        return K8sCli::K8S_CREATED_BY_LABEL_VALUE === $k8sResource->getLabel(K8sCli::K8S_CREATED_BY_LABEL_NAME);
    }
}
