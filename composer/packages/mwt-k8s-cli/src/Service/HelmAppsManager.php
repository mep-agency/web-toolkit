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

namespace Mep\MwtK8sCli\Service;

use DateTimeImmutable;
use Mep\MwtK8sCli\Exception\AppConfigurationNotFoundException;
use Mep\MwtK8sCli\Exception\StopExecutionException;
use RenokiCo\PhpHelm\Helm;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\KubernetesCluster;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class HelmAppsManager
{
    /**
     * @var string
     */
    private const CHART_NAME_PLACEHOLDER = '%chart_name%';

    /**
     * @var string
     */
    private const CHART_VALUES_PATH_PLACEHOLDER = '%chart_values_path%';

    public function __construct(
        private KubernetesCluster $kubernetesCluster,
        private string $cwdPath,
        private string $kubeConfigPath,
    ) {
    }

    public function install(
        string $appName,
        ?string $appEnv,
        string $namespace,
        SymfonyStyle $symfonyStyle,
    ): void {
        $this->runHelmCommandOnGivenEnvironments(
            $appName,
            $appEnv,
            $namespace,
            'install',
            [self::CHART_NAME_PLACEHOLDER, __DIR__.'/../../resources/charts/mwt-app'],
            [
                '--values' => self::CHART_VALUES_PATH_PLACEHOLDER,
            ],
            [],
            $symfonyStyle,
        );
    }

    public function upgrade(
        string $appName,
        ?string $appEnv,
        string $namespace,
        SymfonyStyle $symfonyStyle,
    ): void {
        $this->runHelmCommandOnGivenEnvironments(
            $appName,
            $appEnv,
            $namespace,
            'upgrade',
            [self::CHART_NAME_PLACEHOLDER, __DIR__.'/../../resources/charts/mwt-app'],
            [
                '--values' => self::CHART_VALUES_PATH_PLACEHOLDER,
            ],
            [],
            $symfonyStyle,
        );
    }

    public function delete(
        string $appName,
        ?string $appEnv,
        string $namespace,
        SymfonyStyle $symfonyStyle,
    ): void {
        $this->runHelmCommandOnGivenEnvironments(
            $appName,
            $appEnv,
            $namespace,
            'uninstall',
            [self::CHART_NAME_PLACEHOLDER],
            [],
            [],
            $symfonyStyle,
        );
    }

    /**
     * Apps must be restarted using the K8s API instead of Helm.
     */
    public function restart(string $appName, ?string $appEnv, string $namespace): void
    {
        foreach ($this->getValuesFiles($appName, $appEnv) as $valuesFile) {
            $chartName = $this->getChartName($valuesFile);

            // Faking a "kubectl rollout restart deployment/$NAME --namespace $NAMESPACE"
            $k8sDeployment = $this->kubernetesCluster->getDeploymentByName($chartName, $namespace);
            $template = $k8sDeployment->getTemplate();

            if (! $template instanceof K8sPod) {
                throw new RuntimeException('Unexpected value: app template should be a K8sPod instance.');
            }

            $annotations = $template->getAnnotations();
            $annotations['kubectl.kubernetes.io/restartedAt'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
            $template->setAnnotations($annotations);
            $k8sDeployment->setTemplate($template);

            $k8sDeployment->update();
        }
    }

    /**
     * @param string[]                  $params
     * @param array<int|string, string> $flags
     * @param string[]                  $envs
     */
    private function runHelmCommandOnGivenEnvironments(
        string $appName,
        ?string $appEnv,
        string $namespace,
        string $action,
        array $params,
        array $flags,
        array $envs,
        SymfonyStyle $symfonyStyle,
    ): void {
        foreach ($this->getValuesFiles($appName, $appEnv) as $valuesFile) {
            $chartName = $this->getChartName($valuesFile);
            $chartValuesPath = $valuesFile->getRealPath();

            if (false === $chartValuesPath) {
                throw new RuntimeException('Unexpected value: chart values path should not be "false".');
            }

            // Keep original values intact
            $currentParams = $params;
            $currentFlags = $flags;
            $currentEnvs = $envs;

            $this->replacePlaceholders($currentParams, $currentFlags, $currentEnvs, $chartName, $chartValuesPath);

            $helm = Helm::call(
                $action,
                $currentParams,
                array_merge([
                    '--namespace' => $namespace,
                    '--kubeconfig' => $this->kubeConfigPath,
                ], $currentFlags),
                $currentEnvs,
            );

            $symfonyStyle->section('Running "'.$action.'" action for configuration "'.$chartName.'"');

            $this->runSingleHelmCommand($helm);

            /** @var Process<string> $helm */
            $symfonyStyle->text($helm->getOutput());
        }
    }

    private function getValuesFiles(string $app, ?string $appEnv): Finder
    {
        $appPath = $this->cwdPath.'/apps/'.$app;

        if (! is_dir($appPath)) {
            throw new AppConfigurationNotFoundException($app, $appEnv, $appPath);
        }

        $finder = (new Finder())
            ->in($appPath)
            ->files()
            ->name([
                $app.'-'.(null === $appEnv ? '*' : $appEnv).'.yaml',
                $app.'-'.(null === $appEnv ? '*' : $appEnv).'.yml',
            ])
        ;

        if (! $finder->hasResults()) {
            throw new AppConfigurationNotFoundException($app, $appEnv, $appPath);
        }

        return $finder;
    }

    private function getChartName(SplFileInfo $splFileInfo): string
    {
        return $splFileInfo->getBasename('.'.$splFileInfo->getExtension());
    }

    /**
     * @param string[]                  $params
     * @param array<int|string, string> $flags
     * @param string[]                  $envs
     */
    private function replacePlaceholders(
        array &$params,
        array &$flags,
        array &$envs,
        string $chartName,
        string $chartValuesPath,
    ): void {
        foreach ([&$params, &$flags, &$envs] as &$values) {
            foreach ($values as &$value) {
                $value = str_replace(self::CHART_NAME_PLACEHOLDER, $chartName, $value);
                $value = str_replace(self::CHART_VALUES_PATH_PLACEHOLDER, $chartValuesPath, $value);
            }
        }
    }

    private function runSingleHelmCommand(Helm $helm): void
    {
        /** @var Process<string> $helm */
        $helm->run();

        if (! $helm->isSuccessful()) {
            throw new StopExecutionException(
                'Failed running Helm command: '.PHP_EOL.$helm->getErrorOutput(),
                Command::FAILURE,
            );
        }
    }
}
