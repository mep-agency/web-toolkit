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

namespace Mep\MepWebToolkitK8sCli\Factory;

use RenokiCo\PhpK8s\KubernetesCluster;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class KubernetesClusterFactory
{
    private static KubernetesCluster $clusterInstance;

    private static bool $isConfiguredLocally = false;

    public static function createOrGet(string $configFilePath): KubernetesCluster
    {
        if (! isset(self::$clusterInstance)) {
            self::$clusterInstance = self::create($configFilePath);
        }

        return self::$clusterInstance;
    }

    public static function isConfiguredLocally(): bool
    {
        return self::$isConfiguredLocally;
    }

    private static function create(string $configFilePath): KubernetesCluster
    {
        if (is_file($configFilePath)) {
            self::$isConfiguredLocally = true;

            return KubernetesCluster::fromKubeConfigYamlFile($configFilePath);
        }

        return KubernetesCluster::fromKubeConfigVariable();
    }
}
