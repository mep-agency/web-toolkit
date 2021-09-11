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

use Mep\WebToolkitBundle\Contract\FileStorage\GarbageCollectorInterface;
use Mep\WebToolkitBundle\Contract\FileStorage\ProcessorInterface;
use Mep\WebToolkitBundle\Contract\Mail\TemplateProviderInterface;
use Mep\WebToolkitBundle\WebToolkitBundle;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class WebToolkitExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array<string, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->registerForAutoconfiguration(ProcessorInterface::class)
            ->addTag(WebToolkitBundle::TAG_FILE_STORAGE_PROCESSOR)
        ;

        $containerBuilder->registerForAutoconfiguration(TemplateProviderInterface::class)
            ->addTag(WebToolkitBundle::TAG_MAIL_TEMPLATE_PROVIDER)
        ;

        $containerBuilder->registerForAutoconfiguration(GarbageCollectorInterface::class)
            ->addTag(WebToolkitBundle::TAG_ATTACHMENTS_GARBAGE_COLLECTOR)
        ;

        $phpFileLoader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__.'/../Resources/config'));

        $phpFileLoader->load('services.php');
    }

    public function prepend(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->loadFromExtension('twig', [
            'paths' => [
                // '%kernel.project_dir%/vendor/mep-agency/web-toolkit-bundle/src/Resources/views/bundles/EasyAdminBundle' => 'EasyAdmin',
                realpath(__DIR__.'/..').'/Resources/views/bundles/EasyAdminBundle' => 'EasyAdmin',
            ],
        ]);

        // Enable PHP attributes in Doctrine mappings
        $containerBuilder->loadFromExtension('doctrine', [
            'orm' => [
                'mappings' => [
                    (new ReflectionClass(WebToolkitBundle::class))->getShortName() => 'attribute',
                ],
            ],
        ]);
    }
}
