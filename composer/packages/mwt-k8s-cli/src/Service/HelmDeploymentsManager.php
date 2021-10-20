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
use Mep\MwtK8sCli\Exception\DeploymentConfigurationNotFoundException;
use RenokiCo\PhpHelm\Helm;
use RenokiCo\PhpK8s\Kinds\K8sPod;
use RenokiCo\PhpK8s\KubernetesCluster;
use RuntimeException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class HelmDeploymentsManager
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
        ?SymfonyStyle $symfonyStyle = null,
    ): bool {
        return $this->runHelmCommandForAllDeployments(
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
        ?SymfonyStyle $symfonyStyle = null,
    ): bool {
        return $this->runHelmCommandForAllDeployments(
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
        ?SymfonyStyle $symfonyStyle = null,
    ): bool {
        return $this->runHelmCommandForAllDeployments(
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
     * Deployments must be restarted using the K8s API instead of Helm.
     */
    public function restart(string $appName, ?string $appEnv, string $namespace): void
    {
        foreach ($this->getValuesFiles($appName, $appEnv) as $valuesFile) {
            $chartName = $this->getChartName($valuesFile);

            // Faking a "kubectl rollout restart deployment/$NAME --namespace $NAMESPACE"
            $deployment = $this->kubernetesCluster->getDeploymentByName($chartName, $namespace);
            $template = $deployment->getTemplate();

            if (! $template instanceof K8sPod) {
                throw new RuntimeException('Unexpected value: deployment template should be a K8sPod instance.');
            }

            $annotations = $template->getAnnotations();
            $annotations['kubectl.kubernetes.io/restartedAt'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
            $template->setAnnotations($annotations);
            $deployment->setTemplate($template);

            $deployment->update();
        }
    }

    /**
     * @param string[]                  $params
     * @param array<int|string, string> $flags
     * @param string[]                  $envs
     */
    private function runHelmCommandForAllDeployments(
        string $appName,
        ?string $appEnv,
        string $namespace,
        string $action,
        array $params = [],
        array $flags = [],
        array $envs = [],
        ?SymfonyStyle $symfonyStyle = null,
    ): bool {
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

            $symfonyStyle?->section('Running "'.$action.'" action for configuration "'.$chartName.'"');

            if (! $this->runSingleHelmCommand($helm, $symfonyStyle)) {
                return false;
            }

            /** @var Process<string> $helm */
            $symfonyStyle?->text($helm->getOutput());
        }

        return true;
    }

    private function getValuesFiles(string $deployment, ?string $appEnv): Finder
    {
        $deploymentPath = $this->cwdPath.'/apps/'.$deployment;

        if (! is_dir($deploymentPath)) {
            throw new DeploymentConfigurationNotFoundException($deployment, $appEnv, $deploymentPath);
        }

        $finder = (new Finder())
            ->in($deploymentPath)
            ->files()
            ->name([
                '*'.(null === $appEnv ? '' : '-'.$appEnv).'.yaml',
                '*'.(null === $appEnv ? '' : '-'.$appEnv).'.yml',
            ])
        ;

        if (! $finder->hasResults()) {
            throw new DeploymentConfigurationNotFoundException($deployment, $appEnv, $deploymentPath);
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

    private function runSingleHelmCommand(Helm $helm, ?SymfonyStyle $symfonyStyle = null): bool
    {
        /** @var Process<string> $helm */
        $helm->run();

        if (! $helm->isSuccessful()) {
            $symfonyStyle?->error('Failed running Helm command: '.PHP_EOL.$helm->getErrorOutput());
        }

        return $helm->isSuccessful();
    }
}
