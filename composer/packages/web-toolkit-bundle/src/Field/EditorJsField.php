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
use Mep\WebToolkitBundle\Form\AdminEditorJsType;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class EditorJsField implements FieldInterface
{
    use FieldTrait;

    /**
     * @param null|false|string $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(AdminEditorJsType::class)
            ->setDefaultColumns('col-md-9 col-xxl-7')
            ->addCssClass('mwt-editorjs-field')
            ;
    }
}
