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
use Mep\MepWebToolkitK8sCli\Config\Option;
use Mep\MepWebToolkitK8sCli\Contract\AbstractK8sCommand;
use Mep\MepWebToolkitK8sCli\K8sCli;
use Mep\MepWebToolkitK8sCli\Service\K8sBasicAuthSecretGenerator;
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
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[AsCommand(
    name: self::NAME,
    description: self::DESCRIPTION,
)]
class BasicAuthSecretCreateCommand extends AbstractK8sCommand
{
    /**
     * @var string
     */
    final public const NAME = 'basic-auth-secret:create';

    /**
     * @var string
     */
    final public const DESCRIPTION = 'Creates an HTTP Basic Auth secret associated with the given namespace.';

    public function __construct(
        KubernetesCluster $kubernetesCluster,
        private readonly K8sBasicAuthSecretGenerator $k8sBasicAuthSecretGenerator,
    ) {
        parent::__construct($kubernetesCluster);
    }

    protected function configure(): void
    {
        $this->addArgument(Argument::GENERIC_NAME, InputArgument::REQUIRED, 'A name for the HTTP Basic Auth secret');

        $this->addOption(
            Option::NAMESPACE,
            null,
            InputOption::VALUE_REQUIRED,
            'The namespace to associate with the new HTTP Basic Auth secret',
            K8sCli::K8S_DEFAULT_NAMESPACE,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        /** @var string $pullSecretName */
        $pullSecretName = $input->getArgument(Argument::GENERIC_NAME);
        /** @var string $username */
        $username = $symfonyStyle->ask('Username', null, function ($value) {
            return $this->notNull($value);
        });
        /** @var string $password */
        $password = $symfonyStyle->askHidden('Password', function ($value) {
            return $this->notNull($value);
        });
        /** @var string $namespace */
        $namespace = $input->getOption(Option::NAMESPACE);

        $this->k8sBasicAuthSecretGenerator->generate($pullSecretName, $username, $password, $namespace)->create();

        $symfonyStyle->success('HTTP Basic Auth secret  "'.$pullSecretName.'" created successfully!');

        return Command::SUCCESS;
    }
}
