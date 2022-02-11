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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\DependencyInjection\EasyAdminExtension;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\Configurator\BooleanConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Mep\WebToolkitBundle\Command\FileStorage\GarbageCollectionCommand;
use Mep\WebToolkitBundle\Command\FileStorage\SessionsCreateTableCommand;
use Mep\WebToolkitBundle\Controller\PrivacyConsent\CreateConsentController;
use Mep\WebToolkitBundle\Controller\PrivacyConsent\GetConsentController;
use Mep\WebToolkitBundle\Controller\PrivacyConsent\GetSpecsController;
use Mep\WebToolkitBundle\Controller\PrivacyConsent\ShowHistoryController;
use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\EventListener\AttachmentLifecycleEventListener;
use Mep\WebToolkitBundle\EventListener\ForceSingleInstanceEventListener;
use Mep\WebToolkitBundle\Field\Configurator\AttachmentConfigurator;
use Mep\WebToolkitBundle\Field\Configurator\TranslatableBooleanConfigurator;
use Mep\WebToolkitBundle\Field\Configurator\TranslatableFieldConfigurator;
use Mep\WebToolkitBundle\Field\Configurator\TranslatableFieldPreConfigurator;
use Mep\WebToolkitBundle\Field\Configurator\TypeGuesserConfigurator;
use Mep\WebToolkitBundle\FileStorage\FileStorageManager;
use Mep\WebToolkitBundle\FileStorage\GarbageCollector\AssociationContextGarbageCollector;
use Mep\WebToolkitBundle\FileStorage\Processor\TinifyProcessor;
use Mep\WebToolkitBundle\Form\AdminAttachmentType;
use Mep\WebToolkitBundle\Form\AdminAttachmentUploadApiType;
use Mep\WebToolkitBundle\Form\AdminEditorJsType;
use Mep\WebToolkitBundle\Form\TypeGuesser\AdminAttachmentTypeGuesser;
use Mep\WebToolkitBundle\Form\TypeGuesser\AdminEditorJsTypeGuesser;
use Mep\WebToolkitBundle\Mail\TemplateProvider\DummyTemplateProvider;
use Mep\WebToolkitBundle\Mail\TemplateProvider\TwigTemplateProvider;
use Mep\WebToolkitBundle\Mail\TemplateRenderer;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentCategoryRepository;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentRepository;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentServiceRepository;
use Mep\WebToolkitBundle\Router\AttachmentsAdminApiUrlGenerator;
use Mep\WebToolkitBundle\Serializer\AttachmentNormalizer;
use Mep\WebToolkitBundle\Serializer\EditorJsContentNormalizer;
use Mep\WebToolkitBundle\Service\PrivacyConsentManager;
use Mep\WebToolkitBundle\Twig\AttachmentExtension;
use Mep\WebToolkitBundle\Twig\EditorJsExtension;
use Mep\WebToolkitBundle\Twig\PrivacyConsentExtension;
use Mep\WebToolkitBundle\Twig\TwigFunctionsExtension;
use Mep\WebToolkitBundle\WebToolkitBundle;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->private()
    ;

    // General
    $services->set(WebToolkitBundle::SERVICE_SESSIONS_CREATE_TABLE_COMMAND, SessionsCreateTableCommand::class)
        ->arg(0, new Reference(PdoSessionHandler::class, ContainerInterface::NULL_ON_INVALID_REFERENCE))
        ->tag('console.command')
    ;

    // Single instance support
    $services->set(
        WebToolkitBundle::SERVICE_FORCE_SINGLE_INSTANCE_EVENT_LISTENER,
        ForceSingleInstanceEventListener::class,
    )
        ->tag('doctrine.event_listener', [
            'event' => 'prePersist',
            'priority' => 9999,
        ])
    ;

    // Translatable support
    $services->set(
        WebToolkitBundle::SERVICE_TRANSLATABLE_FIELD_PRE_CONFIGURATOR,
        TranslatableFieldPreConfigurator::class,
    )
        ->arg(0, new Reference(LocaleProviderInterface::class))
        ->arg(1, new Reference(PropertyAccessorInterface::class))
        ->arg(2, new Reference(FormRegistryInterface::class))
        ->arg(3, new Reference(EntityFactory::class))
        ->arg(4, new Reference(WebToolkitBundle::SERVICE_TYPE_GUESSER_CONFIGURATOR))
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR, [
            'priority' => 99999,
        ])
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
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR, [
            'priority' => -9998,
        ])
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

    // File storage support
    $services->set(WebToolkitBundle::SERVICE_FILE_STORAGE_MANAGER, FileStorageManager::class)
        ->arg(0, new Reference(WebToolkitBundle::SERVICE_FILE_STORAGE_DRIVER))
        ->arg(1, new Reference(EntityManagerInterface::class))
        ->arg(2, tagged_iterator(WebToolkitBundle::TAG_FILE_STORAGE_PROCESSOR))
        ->alias(FileStorageManager::class, WebToolkitBundle::SERVICE_FILE_STORAGE_MANAGER)
    ;
    $services->set(WebToolkitBundle::SERVICE_FILE_STORAGE_GARBAGE_COLLECTION_COMMAND, GarbageCollectionCommand::class)
        ->arg(0, new Reference(EntityManagerInterface::class))
        ->arg(1, new Reference(WebToolkitBundle::SERVICE_FILE_STORAGE_MANAGER))
        ->arg(2, tagged_iterator(WebToolkitBundle::TAG_ATTACHMENTS_GARBAGE_COLLECTOR))
        ->tag('console.command')
    ;
    $services->set(
        WebToolkitBundle::SERVICE_ATTACHMENTS_ADMIN_API_URL_GENERATOR,
        AttachmentsAdminApiUrlGenerator::class,
    )
        ->arg(0, new Reference(AdminContextProvider::class))
        ->arg(1, new Reference(AdminUrlGenerator::class))
        ->alias(AttachmentsAdminApiUrlGenerator::class, WebToolkitBundle::SERVICE_ATTACHMENTS_ADMIN_API_URL_GENERATOR)
    ;
    $services->set(
        WebToolkitBundle::SERVICE_ATTACHMENT_LIFECYCLE_EVENT_LISTENER,
        AttachmentLifecycleEventListener::class,
    )
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
            'method' => 'initializeAttachmentProxy',
        ])
        ->tag('doctrine.orm.entity_listener', [
            'entity' => Attachment::class,
            'event' => 'postRemove',
            'method' => 'removeAttachedFile',
        ])
    ;
    $services->set(WebToolkitBundle::SERVICE_ATTACHMENT_NORMALIZER, AttachmentNormalizer::class)
        ->arg(0, new Reference(WebToolkitBundle::SERVICE_FILE_STORAGE_MANAGER))
        ->arg(1, new Reference(EntityManagerInterface::class))
        ->tag('serializer.normalizer')
    ;
    $services->set(WebToolkitBundle::SERVICE_ADMIN_ATTACHMENT_TYPE, AdminAttachmentType::class)
        ->arg(0, new Reference(EntityManagerInterface::class))
        ->arg(1, new Reference(WebToolkitBundle::SERVICE_ATTACHMENTS_ADMIN_API_URL_GENERATOR))
        ->arg(2, new Reference(CsrfTokenManagerInterface::class))
        ->tag('form.type')
    ;
    $services->set(WebToolkitBundle::SERVICE_ADMIN_ATTACHMENT_UPLOAD_API_TYPE, AdminAttachmentUploadApiType::class)
        ->arg(0, new Reference(EntityManagerInterface::class))
        ->arg(1, new Reference(WebToolkitBundle::SERVICE_ATTACHMENTS_ADMIN_API_URL_GENERATOR))
        ->arg(2, new Reference(CsrfTokenManagerInterface::class))
        ->tag('form.type')
    ;
    $services->set(WebToolkitBundle::SERVICE_ADMIN_ATTACHMENT_TYPE_GUESSER, AdminAttachmentTypeGuesser::class)
        ->tag('form.type_guesser')
    ;
    $services->set(WebToolkitBundle::SERVICE_TWIG_ATTACHMENT_EXTENSION, AttachmentExtension::class)
        ->arg(0, new Reference(EntityManagerInterface::class))
        ->arg(1, new Reference(WebToolkitBundle::SERVICE_FILE_STORAGE_MANAGER))
        ->tag('twig.extension')
    ;
    $services->set(WebToolkitBundle::SERVICE_ATTACHMENT_CONFIGURATOR, AttachmentConfigurator::class)
        ->arg(0, new Reference(LocaleProviderInterface::class))
        ->arg(1, new Reference(PropertyAccessorInterface::class))
        ->arg(2, new Reference(FormRegistryInterface::class))
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR)
    ;

    // File storage garbage collectors
    $services->set(WebToolkitBundle::SERVICE_CONTEXT_GARBAGE_COLLECTOR, AssociationContextGarbageCollector::class)
        ->tag(WebToolkitBundle::TAG_ATTACHMENTS_GARBAGE_COLLECTOR)
    ;

    // File storage processors
    $services->set(WebToolkitBundle::SERVICE_TINIFY_PROCESSOR, TinifyProcessor::class)
        ->arg(0, $_ENV['TINIFY_API_KEY'] ?? null)
        ->arg(1, ! isset($_ENV['TINIFY_API_KEY']) && in_array($_ENV['APP_ENV'] ?? 'dev', ['dev', 'test'], true))
        ->tag(WebToolkitBundle::TAG_FILE_STORAGE_PROCESSOR)
    ;

    // EditorJs support
    $services->set(WebToolkitBundle::SERVICE_EDITORJS_CONTENT_NORMALIZER, EditorJsContentNormalizer::class)
        ->arg(0, new Reference(ObjectNormalizer::class))
        ->tag('serializer.normalizer')
    ;
    $services->set(WebToolkitBundle::SERVICE_ADMIN_EDITORJS_TYPE, AdminEditorJsType::class)
        ->arg(0, new Reference(WebToolkitBundle::SERVICE_ATTACHMENTS_ADMIN_API_URL_GENERATOR))
        ->arg(1, new Reference(SerializerInterface::class))
        ->arg(2, new Reference(CsrfTokenManagerInterface::class))
        ->tag('form.type')
    ;
    $services->set(WebToolkitBundle::SERVICE_ADMIN_EDITORJS_TYPE_GUESSER, AdminEditorJsTypeGuesser::class)
        ->tag('form.type_guesser')
    ;
    $services->set(WebToolkitBundle::SERVICE_EDITORJS_EXTENSION, EditorJsExtension::class)
        ->arg(0, new Reference(Environment::class))
        ->tag('twig.extension')
    ;

    // EasyAdminBundle enhancements
    $services->set(WebToolkitBundle::SERVICE_TYPE_GUESSER_CONFIGURATOR, TypeGuesserConfigurator::class)
        ->arg(0, new Reference(FormRegistryInterface::class))
        ->tag(EasyAdminExtension::TAG_FIELD_CONFIGURATOR, [
            'priority' => 99999,
        ])
    ;

    // Extra Twig functions
    $services->set(WebToolkitBundle::SERVICE_TWIG_FUNCTIONS_EXTENSION, TwigFunctionsExtension::class)
        ->arg(0, new Reference(AdapterInterface::class))
        ->arg(1, new Reference(Packages::class))
        ->arg(2, new Reference(KernelInterface::class))
        ->tag('twig.extension')
    ;

    // Privacy consent
    $services->set(WebToolkitBundle::SERVICE_PRIVACY_CONSENT_REPOSITORY, PrivacyConsentRepository::class)
        ->autowire()
        ->tag('doctrine.repository_service')
    ;
    $services->set(
        // Doctrine repositories must be defined using the FQCN
        PrivacyConsentCategoryRepository::class,
    )
        ->autowire()
        ->tag('doctrine.repository_service')
        ->alias(WebToolkitBundle::SERVICE_PRIVACY_CONSENT_CATEGORY_REPOSITORY, PrivacyConsentCategoryRepository::class)
    ;
    $services->set(
        // Doctrine repositories must be defined using the FQCN
        PrivacyConsentServiceRepository::class,
    )
        ->autowire()
        ->tag('doctrine.repository_service')
        ->alias(WebToolkitBundle::SERVICE_PRIVACY_CONSENT_SERVICE_REPOSITORY, PrivacyConsentServiceRepository::class)
    ;
    $services->set(WebToolkitBundle::SERVICE_PRIVACY_CONSENT_MANAGER, PrivacyConsentManager::class)
        ->arg(0, env('PRIVACY_CONSENT_MANAGER_PRIVATE_KEY')->base64())
        ->arg(1, env('PRIVACY_CONSENT_MANAGER_TIMESTAMP_TOLERANCE')->int())
        ->arg(2, new Reference(WebToolkitBundle::SERVICE_PRIVACY_CONSENT_REPOSITORY))
        ->arg(3, new Reference(WebToolkitBundle::SERVICE_PRIVACY_CONSENT_CATEGORY_REPOSITORY))
        ->arg(4, new Reference(WebToolkitBundle::SERVICE_PRIVACY_CONSENT_SERVICE_REPOSITORY))
        ->arg(5, new Reference(EntityManagerInterface::class))
    ;
    $services->set(WebToolkitBundle::SERVICE_PRIVACY_CONSENT_EXTENSION, PrivacyConsentExtension::class)
        ->arg(0, new Reference(UrlGeneratorInterface::class))
        ->tag('twig.extension')
    ;
    $services->set(WebToolkitBundle::SERVICE_PRIVACY_CREATE_CONSENT_CONTROLLER, CreateConsentController::class)
        ->public()
        ->arg(0, new Reference(WebToolkitBundle::SERVICE_PRIVACY_CONSENT_MANAGER))
        ->arg(1, new Reference(RequestStack::class))
        ->arg(2, new Reference(TranslatorInterface::class))
        ->arg(3, new Reference(SerializerInterface::class))
    ;
    $services->set(WebToolkitBundle::SERVICE_PRIVACY_GET_CONSENT_CONTROLLER, GetConsentController::class)
        ->public()
        ->arg(0, new Reference(WebToolkitBundle::SERVICE_PRIVACY_CONSENT_REPOSITORY))
        ->arg(1, new Reference(SerializerInterface::class))
    ;
    $services->set(WebToolkitBundle::SERVICE_PRIVACY_GET_SPECS_CONTROLLER, GetSpecsController::class)
        ->public()
        ->arg(0, new Reference(WebToolkitBundle::SERVICE_PRIVACY_CONSENT_MANAGER))
        ->arg(1, new Reference(SerializerInterface::class))
    ;
    $services->set(WebToolkitBundle::SERVICE_PRIVACY_SHOW_HISTORY_CONTROLLER, ShowHistoryController::class)
        ->public()
        ->arg(0, new Reference(WebToolkitBundle::SERVICE_PRIVACY_CONSENT_REPOSITORY))
        ->arg(1, new Reference(RequestStack::class))
        ->arg(2, new Reference(SerializerInterface::class))
    ;
};
