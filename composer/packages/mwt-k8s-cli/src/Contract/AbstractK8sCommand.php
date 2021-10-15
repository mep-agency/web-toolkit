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

use Mep\MwtK8sCli\Factory\KubernetesClusterFactory;
use Mep\MwtK8sCli\K8sCli;
use RenokiCo\PhpK8s\Exceptions\KubernetesAPIException;
use RenokiCo\PhpK8s\Kinds\K8sResource;
use RenokiCo\PhpK8s\KubernetesCluster;
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

            if ($input->hasArgument('namespace') && ! $this->checkNamespace(
                $input->getArgument('namespace'),
                $input,
                $output,
            )) {
                return Command::INVALID;
            }

            if ($input->hasOption('namespace') && ! $this->checkNamespace(
                $input->getOption('namespace'),
                $input,
                $output,
            )) {
                return Command::INVALID;
            }

            return $this->execute($input, $output);
        });

        return parent::run($input, $output);
    }

    protected function checkNamespace(string $namespaceName, $input, $output): bool
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $force = $input->hasOption('force') ? $input->getOption('force') : false;

        try {
            $k8sNamespace = $this->kubernetesCluster
                ->getNamespaceByName($namespaceName)
            ;
        } catch (KubernetesAPIException $kubernetesapiException) {
            $symfonyStyle->error(
                'Failed checking namespace "'.$namespaceName.'": '.$kubernetesapiException->getPayload()['message'].'.',
            );

            return false;
        }

        if (! $force && ! $this->isCreatedByThisTool($k8sNamespace)) {
            $symfonyStyle->error(
                'The given namespace ("'.$namespaceName.'") was not created by this CLI'.
                ($input->hasOption('force') ? ', use "--force" to skip this check.' : ''),
            );

            return false;
        }

        return true;
    }

    protected function isCreatedByThisTool(K8sResource $k8sResource): bool
    {
        return K8sCli::K8S_CREATED_BY_LABEL_VALUE === $k8sResource->getLabel(K8sCli::K8S_CREATED_BY_LABEL_NAME);
    }
}
