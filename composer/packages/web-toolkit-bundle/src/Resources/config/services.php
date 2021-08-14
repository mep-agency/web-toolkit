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

use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\DependencyInjection\EasyAdminExtension;
use EasyCorp\Bundle\EasyAdminBundle\Field\Configurator\BooleanConfigurator;
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Mep\WebToolkitBundle\DependencyInjection\WebToolkitExtension;
use Mep\WebToolkitBundle\EventListener\ForceSingleInstanceEventListener;
use Mep\WebToolkitBundle\Field\Configurator\TranslatableBooleanConfigurator;
use Mep\WebToolkitBundle\Field\Configurator\TranslatableFieldConfigurator;
use Mep\WebToolkitBundle\Field\Configurator\TranslatableFieldPreConfigurator;
use Mep\WebToolkitBundle\Mail\TemplateProvider\DummyTemplateProvider;
use Mep\WebToolkitBundle\Mail\TemplateProvider\TwigTemplateProvider;
use Mep\WebToolkitBundle\Mail\TemplateRenderer;
use Mep\WebToolkitBundle\Repository\AttachmentRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Twig\Environment;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->private()
    ;

    // Single instance support
    $services->set(WebToolkitExtension::SERVICE_FORCE_SINGLE_INSTANCE_EVENT_LISTENER, ForceSingleInstanceEventListener::class)
        ->tag('doctrine.event_listener', [
            'event' => 'prePersist',
            'priority' => 9999,
        ])
    ;

    // Translatable support
    $services->set(WebToolkitExtension::SERVICE_TRANSLATABLE_FIELD_PRE_CONFIGURATOR, TranslatableFieldPreConfigurator::class)
        ->arg(0, new Reference(LocaleProviderInterface::class))
        ->arg(1, new Reference(PropertyAccessorInterface::class))
        ->arg(2, new Reference(FormRegistryInterface::class))
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR, ['priority' => 99999])
    ;
    $services->set(WebToolkitExtension::SERVICE_TRANSLATABLE_FIELD_CONFIGURATOR, TranslatableFieldConfigurator::class)
        ->arg(0, new Reference(LocaleProviderInterface::class))
        ->arg(1, new Reference(PropertyAccessorInterface::class))
        ->arg(2, new Reference(FormRegistryInterface::class))
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR)
    ;
    $services->set(WebToolkitExtension::SERVICE_TRANSLATABLE_BOOLEAN_CONFIGURATOR, TranslatableBooleanConfigurator::class)
        ->arg(0, new Reference(LocaleProviderInterface::class))
        ->arg(1, new Reference(PropertyAccessorInterface::class))
        ->arg(2, new Reference(FormRegistryInterface::class))
        ->arg(3, new Reference(BooleanConfigurator::class))
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR, ['priority' => -9998])
    ;

    // Mail templates support
    $services->set(WebToolkitExtension::SERVICE_TEMPLATE_RENDERER, TemplateRenderer::class)
        ->arg(0, tagged_iterator(WebToolkitExtension::TAG_MAIL_TEMPLATE_PROVIDER))
        ->alias(TemplateRenderer::class, WebToolkitExtension::SERVICE_TEMPLATE_RENDERER)
    ;
    $services->set(WebToolkitExtension::SERVICE_TWIG_TEMPLATE_PROVIDER, TwigTemplateProvider::class)
        ->arg(0, new Reference(Environment::class))
        ->tag(WebToolkitExtension::TAG_MAIL_TEMPLATE_PROVIDER)
    ;
    $services->set(WebToolkitExtension::SERVICE_DUMMY_TEMPLATE_PROVIDER, DummyTemplateProvider::class)
        ->tag(WebToolkitExtension::TAG_MAIL_TEMPLATE_PROVIDER)
    ;

    // Attachments support
    $services->set(WebToolkitExtension::SERVICE_ATTACHMENT_REPOSITORY, AttachmentRepository::class)
        ->arg(0, new Reference(ManagerRegistry::class))
        ->tag('doctrine.repository_service')
        ->alias(AttachmentRepository::class, WebToolkitExtension::SERVICE_ATTACHMENT_REPOSITORY)
    ;
};
