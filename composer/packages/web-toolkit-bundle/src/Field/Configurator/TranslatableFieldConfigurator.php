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
    public function configure(FieldDto $fieldDto, EntityDto $entityDto, AdminContext $adminContext): void
    {
        $fieldDto->setFormTypeOption('property_path', $this->getFieldPropertyPath($fieldDto, $entityDto));

        $value = $this->rebuildValueOption($fieldDto, $entityDto);
        $fieldDto->setValue($value);
        $fieldDto->setFormattedValue($value);

        $templatePath = $this->rebuildTemplatePathOption($adminContext, $fieldDto, $entityDto);
        $fieldDto->setTemplatePath($templatePath);
    }

    /**
     * The CommonPreConfigurator fails building values for translatable properties.
     *
     * @see CommonPreConfigurator::buildValueOption()
     *
     * @return null|mixed
     */
    private function rebuildValueOption(FieldDto $fieldDto, EntityDto $entityDto)
    {
        $entityInstance = $entityDto->getInstance()
            ->translate(null, false)
        ;
        $propertyName = $fieldDto->getProperty();

        if (! $this->propertyAccessor->isReadable($entityInstance, $propertyName)) {
            return null;
        }

        return $this->propertyAccessor->getValue($entityInstance, $propertyName);
    }

    /**
     * The CommonPreConfigurator fails building template path for translatable properties.
     *
     * @see CommonPreConfigurator::buildTemplatePathOption()
     */
    private function rebuildTemplatePathOption(
        AdminContext $adminContext,
        FieldDto $fieldDto,
        EntityDto $entityDto,
    ): ?string {
        $labelInaccessibleTemplatePath = $adminContext->getTemplatePath('label/inaccessible');

        $templatePath = $fieldDto->getTemplatePath();

        if (! in_array($templatePath, [null, $labelInaccessibleTemplatePath], true)) {
            return $templatePath;
        }

        $isPropertyReadable = $this->propertyAccessor->isReadable(
            $entityDto->getInstance()
                ->translate(null, false),
            $fieldDto->getProperty(),
        );
        if (! $isPropertyReadable) {
            return $labelInaccessibleTemplatePath;
        }

        $templateName = $fieldDto->getTemplateName();

        if (null === $templateName) {
            throw new RuntimeException(sprintf(
                'Fields must define either their templateName or their templatePath. None given for "%s" field.',
                $fieldDto->getProperty(),
            ));
        }

        return $adminContext->getTemplatePath($templateName);
    }
}
