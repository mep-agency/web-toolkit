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

use Mep\WebToolkitBundle\Contract\Mail\TemplateProviderInterface;
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
    public const REFERENCE_PREFIX = 'mep_web_toolkit.';

    public const SERVICE_FORCE_SINGLE_INSTANCE_EVENT_LISTENER = self::REFERENCE_PREFIX . 'force_single_instance_event_listener';

    public const SERVICE_TRANSLATABLE_FIELD_PRE_CONFIGURATOR = self::REFERENCE_PREFIX . 'translatable_field_pre_configurator';

    public const SERVICE_TRANSLATABLE_FIELD_CONFIGURATOR = self::REFERENCE_PREFIX . 'translatable_field_configurator';

    public const SERVICE_TRANSLATABLE_BOOLEAN_CONFIGURATOR = self::REFERENCE_PREFIX . 'translatable_boolean_configurator';

    public const SERVICE_TEMPLATE_RENDERER = self::REFERENCE_PREFIX . 'template_renderer';

    public const SERVICE_TWIG_TEMPLATE_PROVIDER = self::REFERENCE_PREFIX . 'twig_template_provider';

    public const SERVICE_DUMMY_TEMPLATE_PROVIDER = self::REFERENCE_PREFIX . 'dummy_template_provider';

    public const SERVICE_ATTACHMENT_REPOSITORY = self::REFERENCE_PREFIX . 'attachment_repository';

    public const TAG_MAIL_TEMPLATE_PROVIDER = self::REFERENCE_PREFIX . 'mail_template_provider';

    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(TemplateProviderInterface::class)
            ->addTag(self::TAG_MAIL_TEMPLATE_PROVIDER);

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
