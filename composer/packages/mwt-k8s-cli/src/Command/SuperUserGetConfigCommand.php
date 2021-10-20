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

use Mep\MwtK8sCli\Config\Argument;
use Mep\MwtK8sCli\Config\Option;
use Mep\MwtK8sCli\Contract\AbstractK8sCommand;
use Mep\MwtK8sCli\K8sCli;
use Mep\MwtK8sCli\Service\K8sConfigGenerator;
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
    name: 'super-user:get-config',
    description: 'Generates a config file for the given super-user service account.',
)]
class SuperUserGetConfigCommand extends AbstractK8sCommand
{
    public function __construct(
        KubernetesCluster $kubernetesCluster,
        private K8sConfigGenerator $k8sConfigGenerator,
        private string $defaultOutputPath,
    ) {
        parent::__construct($kubernetesCluster);
    }

    protected function configure(): void
    {
        $this->addArgument(Argument::SERVICE_ACCOUNT, InputArgument::REQUIRED, 'The service account name');

        $this->addOption(Option::OUTPUT, 'o', InputOption::VALUE_REQUIRED, 'An output file', $this->defaultOutputPath);
        $this->addOption(
            Option::NAMESPACE,
            null,
            InputOption::VALUE_REQUIRED,
            'The namespace to associate with the new service account',
            K8sCli::K8S_DEFAULT_NAMESPACE,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $serviceAccountName = $input->getArgument(Argument::SERVICE_ACCOUNT);
        $namespace = $input->getOption(Option::NAMESPACE);
        $outputPath = $input->getOption(Option::OUTPUT);

        /** @phpstan-ignore-next-line The vendor lib uses magic calls for undocumented resources */
        $secretName = $this->kubernetesCluster
            ->getServiceAccountByName($serviceAccountName, $namespace)
            ->getSecrets()[0]['name']
        ;
        $secretData = $this->kubernetesCluster
            ->getSecretByName($secretName, $namespace)
            ->getData(true)
        ;

        // The library doesn't return the clean API url, so we generate one with no path nor query parameters
        $url = trim($this->kubernetesCluster->getCallableUrl('', []), '?');

        $this->k8sConfigGenerator->generateConfigFile(
            $outputPath,
            base64_encode($secretData['ca.crt']),
            $url,
            $secretData['token'],
            $secretData[Option::NAMESPACE],
        );

        $symfonyStyle->success('Super-user configuration file created successfully!');

        return Command::SUCCESS;
    }
}
