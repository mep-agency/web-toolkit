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

namespace Mep\WebToolkitBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
final class WebToolkitBundle extends Bundle
{
    /**
     * @var string
     */
    public const REFERENCE_PREFIX = 'mep_web_toolkit.';

    // Tags
    /**
     * @var string
     */
    public const TAG_FILE_STORAGE_PROCESSOR = self::REFERENCE_PREFIX.'file_storage_processor';

    /**
     * @var string
     */
    public const TAG_MAIL_TEMPLATE_PROVIDER = self::REFERENCE_PREFIX.'mail_template_provider';

    /**
     * @var string
     */
    public const TAG_ATTACHMENTS_GARBAGE_COLLECTOR = self::REFERENCE_PREFIX.'attachments_garbage_collector';

    // Single instance support
    /**
     * @var string
     */
    public const SERVICE_FORCE_SINGLE_INSTANCE_EVENT_LISTENER = self::REFERENCE_PREFIX.'force_single_instance_event_listener';

    // Translatable support
    /**
     * @var string
     */
    public const SERVICE_TRANSLATABLE_FIELD_PRE_CONFIGURATOR = self::REFERENCE_PREFIX.'translatable_field_pre_configurator';

    /**
     * @var string
     */
    public const SERVICE_TRANSLATABLE_FIELD_CONFIGURATOR = self::REFERENCE_PREFIX.'translatable_field_configurator';

    /**
     * @var string
     */
    public const SERVICE_TRANSLATABLE_BOOLEAN_CONFIGURATOR = self::REFERENCE_PREFIX.'translatable_boolean_configurator';

    // Mail templates support
    /**
     * @var string
     */
    public const SERVICE_TEMPLATE_RENDERER = self::REFERENCE_PREFIX.'template_renderer';

    /**
     * @var string
     */
    public const SERVICE_TWIG_TEMPLATE_PROVIDER = self::REFERENCE_PREFIX.'twig_template_provider';

    /**
     * @var string
     */
    public const SERVICE_DUMMY_TEMPLATE_PROVIDER = self::REFERENCE_PREFIX.'dummy_template_provider';

    // File storage support
    /**
     * @var string
     */
    public const SERVICE_FILE_STORAGE_MANAGER = self::REFERENCE_PREFIX.'file_storage_manager';

    /**
     * @var string
     */
    public const SERVICE_FILE_STORAGE_GARBAGE_COLLECTION_COMMAND = self::REFERENCE_PREFIX.'file_storage_garbage_collection_command';

    /**
     * @var string
     */
    public const SERVICE_ATTACHMENTS_ADMIN_API_URL_GENERATOR = self::REFERENCE_PREFIX.'service_attachments_admin_api_url_generator';

    /**
     * @var string
     */
    public const SERVICE_UPLOADED_FILE_PROCESSOR = self::REFERENCE_PREFIX.'uploaded_file_processor';

    /**
     * @var string
     */
    public const SERVICE_FILE_STORAGE_DRIVER = self::REFERENCE_PREFIX.'file_storage_driver';

    /**
     * @var string
     */
    public const SERVICE_ATTACHMENT_LIFECYCLE_EVENT_LISTENER = self::REFERENCE_PREFIX.'attachment_lifecycle_event_listener';

    /**
     * @var string
     */
    public const SERVICE_ATTACHMENT_NORMALIZER = self::REFERENCE_PREFIX.'attachment_normalizer';

    /**
     * @var string
     */
    public const SERVICE_ADMIN_ATTACHMENT_TYPE = self::REFERENCE_PREFIX.'admin_attachment_type';

    /**
     * @var string
     */
    public const SERVICE_ADMIN_ATTACHMENT_UPLOAD_API_TYPE = self::REFERENCE_PREFIX.'admin_attachment_upload_type';

    /**
     * @var string
     */
    public const SERVICE_ADMIN_ATTACHMENT_TYPE_GUESSER = self::REFERENCE_PREFIX.'admin_attachment_type_guesser';

    /**
     * @var string
     */
    public const SERVICE_TWIG_ATTACHMENT_EXTENSION = self::REFERENCE_PREFIX.'twig_attachment_extension';

    /**
     * @var string
     */
    public const SERVICE_ATTACHMENT_CONFIGURATOR = self::REFERENCE_PREFIX.'attachment_configurator';

    // File storage garbage collectors
    /**
     * @var string
     */
    public const SERVICE_CONTEXT_GARBAGE_COLLECTOR = self::REFERENCE_PREFIX.'context_garbage_collector';

    // File storage processors
    /**
     * @var string
     */
    public const SERVICE_TINIFY_PROCESSOR = self::REFERENCE_PREFIX.'tinify_processor';

    // EditorJs support
    /**
     * @var string
     */
    public const SERVICE_EDITORJS_CONTENT_NORMALIZER = self::REFERENCE_PREFIX.'editorjs_content_normalizer';

    /**
     * @var string
     */
    public const SERVICE_ADMIN_EDITORJS_TYPE = self::REFERENCE_PREFIX.'admin_editorjs_type';

    /**
     * @var string
     */
    public const SERVICE_ADMIN_EDITORJS_TYPE_GUESSER = self::REFERENCE_PREFIX.'admin_editorjs_type_guesser';

    /**
     * @var string
     */
    public const SERVICE_EDITORJS_EXTENSION = self::REFERENCE_PREFIX.'editorjs_extension';

    // EasyAdminBundle enhancements
    /**
     * @var string
     */
    public const SERVICE_TYPE_GUESSER_CONFIGURATOR = self::REFERENCE_PREFIX.'type_guesser_configurator';

    // Extra Twig functions
    /**
     * @var string
     */
    public const SERVICE_TWIG_FUNCTIONS_EXTENSION = self::REFERENCE_PREFIX.'twig_functions_extension';
}
