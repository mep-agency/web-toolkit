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

namespace Mep\WebToolkitBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class WebToolkitExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.php');
    }

    public function prepend(ContainerBuilder $container)
    {
        $container->loadFromExtension('twig', [
            'paths' => [
                // '%kernel.project_dir%/vendor/mep-agency/web-toolkit-bundle/src/Resources/views/bundles/EasyAdminBundle' => 'EasyAdmin',
                realpath(__DIR__ . '/..') . '/Resources/views/bundles/EasyAdminBundle' => 'EasyAdmin',
            ],
        ]);
    }
}