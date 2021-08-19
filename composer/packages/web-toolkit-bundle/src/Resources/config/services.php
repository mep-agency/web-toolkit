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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\DependencyInjection\EasyAdminExtension;
use EasyCorp\Bundle\EasyAdminBundle\Field\Configurator\BooleanConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\EventListener\AttachmentLifecycleEventListener;
use Mep\WebToolkitBundle\EventListener\ForceSingleInstanceEventListener;
use Mep\WebToolkitBundle\Field\Configurator\TranslatableBooleanConfigurator;
use Mep\WebToolkitBundle\Field\Configurator\TranslatableFieldConfigurator;
use Mep\WebToolkitBundle\Field\Configurator\TranslatableFieldPreConfigurator;
use Mep\WebToolkitBundle\FileStorage\FileStorageManager;
use Mep\WebToolkitBundle\FileStorage\Processor\UploadedFileProcessor;
use Mep\WebToolkitBundle\Form\AdminAttachmentUploadApiType;
use Mep\WebToolkitBundle\Form\AdminAttachmentType;
use Mep\WebToolkitBundle\Mail\TemplateProvider\DummyTemplateProvider;
use Mep\WebToolkitBundle\Mail\TemplateProvider\TwigTemplateProvider;
use Mep\WebToolkitBundle\Mail\TemplateRenderer;
use Mep\WebToolkitBundle\Repository\AttachmentRepository;
use Mep\WebToolkitBundle\Twig\AttachmentExtension;
use Mep\WebToolkitBundle\WebToolkitBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
    $services->set(WebToolkitBundle::SERVICE_FORCE_SINGLE_INSTANCE_EVENT_LISTENER, ForceSingleInstanceEventListener::class)
        ->tag('doctrine.event_listener', [
            'event' => 'prePersist',
            'priority' => 9999,
        ])
    ;

    // Translatable support
    $services->set(WebToolkitBundle::SERVICE_TRANSLATABLE_FIELD_PRE_CONFIGURATOR, TranslatableFieldPreConfigurator::class)
        ->arg(0, new Reference(LocaleProviderInterface::class))
        ->arg(1, new Reference(PropertyAccessorInterface::class))
        ->arg(2, new Reference(FormRegistryInterface::class))
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR, ['priority' => 99999])
    ;
    $services->set(WebToolkitBundle::SERVICE_TRANSLATABLE_FIELD_CONFIGURATOR, TranslatableFieldConfigurator::class)
        ->arg(0, new Reference(LocaleProviderInterface::class))
        ->arg(1, new Reference(PropertyAccessorInterface::class))
        ->arg(2, new Reference(FormRegistryInterface::class))
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR)
    ;
    $services->set(WebToolkitBundle::SERVICE_TRANSLATABLE_BOOLEAN_CONFIGURATOR, TranslatableBooleanConfigurator::class)
        ->arg(0, new Reference(LocaleProviderInterface::class))
        ->arg(1, new Reference(PropertyAccessorInterface::class))
        ->arg(2, new Reference(FormRegistryInterface::class))
        ->arg(3, new Reference(BooleanConfigurator::class))
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR, ['priority' => -9998])
    ;

    // Mail templates support
    $services->set(WebToolkitBundle::SERVICE_TEMPLATE_RENDERER, TemplateRenderer::class)
        ->arg(0, tagged_iterator(WebToolkitBundle::TAG_MAIL_TEMPLATE_PROVIDER))
        ->alias(TemplateRenderer::class, WebToolkitBundle::SERVICE_TEMPLATE_RENDERER)
    ;
    $services->set(WebToolkitBundle::SERVICE_TWIG_TEMPLATE_PROVIDER, TwigTemplateProvider::class)
        ->arg(0, new Reference(Environment::class))
        ->tag(WebToolkitBundle::TAG_MAIL_TEMPLATE_PROVIDER)
    ;
    $services->set(WebToolkitBundle::SERVICE_DUMMY_TEMPLATE_PROVIDER, DummyTemplateProvider::class)
        ->tag(WebToolkitBundle::TAG_MAIL_TEMPLATE_PROVIDER)
    ;

    // Attachments support
    $services->set(WebToolkitBundle::SERVICE_FILE_STORAGE_MANAGER, FileStorageManager::class)
        ->arg(0, new Reference(WebToolkitBundle::SERVICE_FILE_STORAGE_DRIVER))
        ->arg(1, new Reference(EntityManagerInterface::class))
        ->arg(2, tagged_iterator(WebToolkitBundle::TAG_FILE_STORAGE_PROCESSOR))
        ->alias(FileStorageManager::class, WebToolkitBundle::SERVICE_FILE_STORAGE_MANAGER)
    ;
    $services->set(WebToolkitBundle::SERVICE_UPLOADED_FILE_PROCESSOR, UploadedFileProcessor::class)
        ->tag(WebToolkitBundle::TAG_FILE_STORAGE_PROCESSOR, ['priority' => -9999])
    ;
    $services->set(WebToolkitBundle::SERVICE_ATTACHMENT_REPOSITORY, AttachmentRepository::class)
        ->arg(0, new Reference(ManagerRegistry::class))
        ->tag('doctrine.repository_service')
        ->alias(AttachmentRepository::class, WebToolkitBundle::SERVICE_ATTACHMENT_REPOSITORY)
    ;
    $services->set(WebToolkitBundle::SERVICE_ATTACHMENT_LIFECYCLE_EVENT_LISTENER, AttachmentLifecycleEventListener::class)
        ->arg(0, new Reference(WebToolkitBundle::SERVICE_FILE_STORAGE_DRIVER))
        ->arg(1, new Reference(ValidatorInterface::class))
        ->tag('doctrine.orm.entity_listener', [
            'entity' => Attachment::class,
            'event' => 'prePersist',
            'method' => 'validate',
        ])
        ->tag('doctrine.orm.entity_listener', [
            'entity' => Attachment::class,
            'event' => 'preRemove',
            'method' => 'removeAttachedFile',
        ])
    ;
    $services->set(WebToolkitBundle::SERVICE_ADMIN_ATTACHMENT_TYPE, AdminAttachmentType::class)
        ->arg(0, new Reference(WebToolkitBundle::SERVICE_ATTACHMENT_REPOSITORY))
        ->arg(1, new Reference(AdminUrlGenerator::class))
        ->tag('form.type')
    ;
    $services->set(WebToolkitBundle::SERVICE_ADMIN_ATTACHMENT_UPLOAD_API_TYPE, AdminAttachmentUploadApiType::class)
        ->arg(0, new Reference(WebToolkitBundle::SERVICE_ATTACHMENT_REPOSITORY))
        ->arg(1, new Reference(AdminUrlGenerator::class))
        ->tag('form.type')
    ;
    $services->set(WebToolkitBundle::SERVICE_TWIG_ATTACHMENT_EXTENSION, AttachmentExtension::class)
        ->arg(0, new Reference(WebToolkitBundle::SERVICE_ATTACHMENT_REPOSITORY))
        ->arg(1, new Reference(FileStorageManager::class))
        ->tag('twig.extension')
    ;
};
