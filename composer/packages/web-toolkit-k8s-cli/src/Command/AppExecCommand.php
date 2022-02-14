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

use Mep\MepWebToolkitK8sCli\Contract\AbstractHelmCommand;
use Mep\MepWebToolkitK8sCli\Service\HelmAppsManager;
use RenokiCo\PhpK8s\KubernetesCluster;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
#[AsCommand(
    name: self::NAME,
    description: self::DESCRIPTION,
)]
class AppExecCommand extends AbstractHelmCommand
{
    /**
     * @var string
     */
    final public const NAME = 'app:exec';

    /**
     * @var string
     */
    final public const DESCRIPTION = 'Opens a bash shell inside one pod of the given app';

    public function __construct(
        KubernetesCluster $kubernetesCluster,
        HelmAppsManager $helmAppsManager,
        private readonly string $kubeConfigPath,
    ) {
        parent::__construct($kubernetesCluster, $helmAppsManager, false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $appName = $this->getAppName($input);
        $appEnvironment = $this->getAppEnvironment($input, $output);
        $namespace = $this->getNamespace($input);

        $process = new Process(
            [
                'kubectl',
                '--kubeconfig',
                $this->kubeConfigPath,
                '--namespace',
                $namespace,
                'exec',
                '-it',
                'deploy/'.$appName.'-'.$appEnvironment,
                '--',
                '/bin/bash',
            ],
            null,
            null,
            null,
            null,
        );
        $process->setTty(true);

        $process->run();

        return Command::SUCCESS;
    }
}
