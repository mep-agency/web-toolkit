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

namespace Mep\MepWebToolkitK8sCli\Contract;

use Mep\MepWebToolkitK8sCli\Config\Argument;
use Mep\MepWebToolkitK8sCli\Config\Option;
use Mep\MepWebToolkitK8sCli\Exception\StopExecutionException;
use Mep\MepWebToolkitK8sCli\Factory\KubernetesClusterFactory;
use Mep\MepWebToolkitK8sCli\K8sCli;
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
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
abstract class AbstractK8sCommand extends Command
{
    public function __construct(
        protected KubernetesCluster $kubernetesCluster,
    ) {
        parent::__construct();
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        // Always run validation before execution
        $this->setCode(function (InputInterface $input, OutputInterface $output): int {
            if (! KubernetesClusterFactory::isConfiguredLocally()) {
                $symfonyStyle = new SymfonyStyle($input, $output);

                $symfonyStyle->error(
                    'This tool has loaded the kubectl configuration automatically, this may be invalid or risky.'.PHP_EOL.PHP_EOL.'Please create a local configuration file instead:'.PHP_EOL.'> php bin/mwt-k8s config:create --help',
                );

                return Command::INVALID;
            }

            try {
                // Check given namespace names
                $namespaceNames = [];

                if ($input->hasArgument(Argument::NAMESPACE)) {
                    $namespaceNames[] = $input->getArgument(Argument::NAMESPACE);
                }

                if ($input->hasOption(Option::NAMESPACE)) {
                    $namespaceNames[] = $input->getOption(Option::NAMESPACE);
                }

                /** @var string $namespaceName */
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
                    switch ($stopExecutionException->getCode()) {
                        case Command::SUCCESS:
                            $symfonyStyle->info($stopExecutionException->getMessage());

                            break;
                        case Command::INVALID:
                            $symfonyStyle->warning($stopExecutionException->getMessage());

                            break;
                        default:
                            $symfonyStyle->error($stopExecutionException->getMessage());

                            break;
                    }
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
        $hasForce = $input->hasOption(Option::FORCE);
        $forceValue = $hasForce && $input->getOption(Option::FORCE);

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
