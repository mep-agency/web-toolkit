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

namespace Mep\WebToolkitBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Mep\WebToolkitBundle\Form\AdminAttachmentType;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentField implements FieldInterface
{
    use FieldTrait;

    /**
     * @param string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(AdminAttachmentType::class)
            ->setFormTypeOption(AdminAttachmentType::PROPERTY_PATH, $propertyName)
            ->setTemplatePath('@WebToolkit/admin/crud/field/attachment.html.twig')
            ->setDefaultColumns('col-md-6 col-xxl-5')
            ->addCssClass('mwt-attachment-field')
            ->addCssFiles('bundles/webtoolkit/attachment-field.css')
            ->addJsFiles('bundles/webtoolkit/attachment-field.js')
            ;
    }
}
