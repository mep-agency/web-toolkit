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

namespace Mep\WebToolkitBundle\Field\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\Configurator\CommonPreConfigurator;
use Mep\WebToolkitBundle\Contract\Field\Configurator\AbstractTranslatableFieldConfigurator;
use RuntimeException;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
final class TranslatableFieldConfigurator extends AbstractTranslatableFieldConfigurator
{
    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $field->setFormTypeOption(
            'property_path',
            $this->getFieldPropertyPath($field, $entityDto),
        );

        $value = $this->rebuildValueOption($field, $entityDto);
        $field->setValue($value);
        $field->setFormattedValue($value);

        $templatePath = $this->rebuildTemplatePathOption($context, $field, $entityDto);
        $field->setTemplatePath($templatePath);
    }

    /**
     * The CommonPreConfigurator fails building values for translatable properties.
     *
     * @see CommonPreConfigurator::buildValueOption()
     */
    private function rebuildValueOption(FieldDto $field, EntityDto $entityDto)
    {
        $entityInstance = $entityDto->getInstance()->translate(null, false);
        $propertyName = $field->getProperty();

        if (!$this->propertyAccessor->isReadable($entityInstance, $propertyName)) {
            return null;
        }

        return $this->propertyAccessor->getValue($entityInstance, $propertyName);
    }

    /**
     * The CommonPreConfigurator fails building template path for translatable properties.
     *
     * @see CommonPreConfigurator::buildTemplatePathOption()
     */
    private function rebuildTemplatePathOption(AdminContext $adminContext, FieldDto $field, EntityDto $entityDto): string
    {
        $labelInaccessibleTemplatePath = $adminContext->getTemplatePath('label/inaccessible');

        if (! in_array(
            $templatePath = $field->getTemplatePath(),
            [null, $labelInaccessibleTemplatePath],
            true
        )) {
            return $templatePath;
        }

        $isPropertyReadable = $this->propertyAccessor->isReadable($entityDto->getInstance()->translate(null, false), $field->getProperty());
        if (!$isPropertyReadable) {
            return $labelInaccessibleTemplatePath;
        }

        if (null === $templateName = $field->getTemplateName()) {
            throw new RuntimeException(sprintf('Fields must define either their templateName or their templatePath. None given for "%s" field.', $field->getProperty()));
        }

        return $adminContext->getTemplatePath($templateName);
    }
}