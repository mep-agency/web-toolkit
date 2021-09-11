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
use Mep\WebToolkitBundle\Contract\Field\Configurator\AbstractTranslatableFieldConfigurator;
use Mep\WebToolkitBundle\Dto\AttachmentAssociationContextDto;
use Mep\WebToolkitBundle\Field\AttachmentField;
use Mep\WebToolkitBundle\Form\AdminAttachmentType;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentConfigurator extends AbstractTranslatableFieldConfigurator
{
    public function supports(FieldDto $fieldDto, EntityDto $entityDto): bool
    {
        return AttachmentField::class === $fieldDto->getFieldFqcn();
    }

    public function configure(FieldDto $fieldDto, EntityDto $entityDto, AdminContext $adminContext): void
    {
        $entityFqcn = parent::supports($fieldDto, $entityDto) ?
            $this->getTranslationFqcn($entityDto) : $entityDto->getFqcn();

        $fieldDto->setFormTypeOption(
            AdminAttachmentType::CONTEXT,
            (string) (new AttachmentAssociationContextDto($entityFqcn, $fieldDto->getProperty(),)),
        );
    }
}
