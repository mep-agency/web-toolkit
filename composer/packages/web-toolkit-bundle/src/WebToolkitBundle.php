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
    public const REFERENCE_PREFIX = 'mep_web_toolkit.';

    public const TAG_FILE_STORAGE_PROCESSOR = self::REFERENCE_PREFIX . 'file_storage_processor';
    public const TAG_MAIL_TEMPLATE_PROVIDER = self::REFERENCE_PREFIX . 'mail_template_provider';

    // Single instance support
    public const SERVICE_FORCE_SINGLE_INSTANCE_EVENT_LISTENER = self::REFERENCE_PREFIX . 'force_single_instance_event_listener';

    // Translatable support
    public const SERVICE_TRANSLATABLE_FIELD_PRE_CONFIGURATOR = self::REFERENCE_PREFIX . 'translatable_field_pre_configurator';
    public const SERVICE_TRANSLATABLE_FIELD_CONFIGURATOR = self::REFERENCE_PREFIX . 'translatable_field_configurator';
    public const SERVICE_TRANSLATABLE_BOOLEAN_CONFIGURATOR = self::REFERENCE_PREFIX . 'translatable_boolean_configurator';

    // Attachments support
    public const SERVICE_FILE_STORAGE_MANAGER = self::REFERENCE_PREFIX . 'file_storage_manager';
    public const SERVICE_UPLOADED_FILE_PROCESSOR = self::REFERENCE_PREFIX . 'uploaded_file_processor';
    public const SERVICE_FILE_STORAGE_DRIVER = self::REFERENCE_PREFIX . 'file_storage_driver';
    public const SERVICE_ATTACHMENT_REPOSITORY = self::REFERENCE_PREFIX . 'attachment_repository';
    public const SERVICE_ATTACHMENT_LIFECYCLE_EVENT_LISTENER = self::REFERENCE_PREFIX . 'attachment_lifecycle_event_listener';
    public const SERVICE_ADMIN_ATTACHMENT_TYPE = self::REFERENCE_PREFIX . 'admin_attachment_type';
    public const SERVICE_ADMIN_ATTACHMENT_UPLOAD_API_TYPE = self::REFERENCE_PREFIX . 'admin_attachment_upload_type';
    public const SERVICE_ADMIN_ATTACHMENT_TYPE_GUESSER = self::REFERENCE_PREFIX . 'admin_attachment_type_guesser';
    public const SERVICE_ATTACHMENT_FIELD_CONFIGURATOR = self::REFERENCE_PREFIX . 'attachment_field_configurator';
    public const SERVICE_TWIG_ATTACHMENT_EXTENSION = self::REFERENCE_PREFIX . 'service_twig_attachment_extension';

    // FileStorage processors
    public const SERVICE_TINIFY_PROCESSOR = self::REFERENCE_PREFIX . 'tinify_processor';

    // Mail templates support
    public const SERVICE_TEMPLATE_RENDERER = self::REFERENCE_PREFIX . 'template_renderer';
    public const SERVICE_TWIG_TEMPLATE_PROVIDER = self::REFERENCE_PREFIX . 'twig_template_provider';
    public const SERVICE_DUMMY_TEMPLATE_PROVIDER = self::REFERENCE_PREFIX . 'dummy_template_provider';

    // EasyAdminBundle enhancements
    public const SERVICE_TYPE_GUESSER_CONFIGURATOR = self::REFERENCE_PREFIX . 'type_guesser_configurator';
}