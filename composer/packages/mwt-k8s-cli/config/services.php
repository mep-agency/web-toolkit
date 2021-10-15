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

use Mep\MwtK8sCli\Application;
use Mep\MwtK8sCli\Command\ConfigCreateCommand;
use Mep\MwtK8sCli\Command\SuperUserGetConfigCommand;
use Mep\MwtK8sCli\Factory\KubernetesClusterFactory;
use RenokiCo\PhpK8s\KubernetesCluster;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->private()
    ;

    $services
        ->set('application', Application::class)
        ->arg(0, tagged_iterator('console.command'))
        ->public()
    ;

    $services->set('k8s_cluster', KubernetesCluster::class)
        ->arg(0, '%working_dir%/kube-config.yaml')
        ->factory(function (string $configFilePath): KubernetesCluster {
            return KubernetesClusterFactory::createOrGet($configFilePath);
        })
        ->alias(KubernetesCluster::class, 'k8s_cluster')
    ;

    $services->load('Mep\\MwtK8sCli\\Service\\', '../src/Service/*');

    $services->load('Mep\\MwtK8sCli\\Command\\', '../src/Command/*');

    $services->get(ConfigCreateCommand::class)
        ->arg('$configFilePath', '%working_dir%/kube-config.yaml')
    ;

    $services->get(SuperUserGetConfigCommand::class)
        ->arg('$defaultOutputPath', '%working_dir%/su-config.yaml')
    ;
};
