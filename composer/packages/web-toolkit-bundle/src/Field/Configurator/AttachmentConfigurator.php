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
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Mep\WebToolkitBundle\Contract\Field\Configurator\AbstractTranslatableFieldConfigurator;
use Mep\WebToolkitBundle\Dto\AttachmentAssociationContextDto;
use Mep\WebToolkitBundle\Field\AttachmentField;
use Mep\WebToolkitBundle\Form\AdminAttachmentType;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentConfigurator extends AbstractTranslatableFieldConfigurator
{
    public function __construct(
        protected LocaleProviderInterface   $localeProvider,
        protected PropertyAccessorInterface $propertyAccessor,
        protected FormRegistryInterface     $formRegistry,
    ) {
        parent::__construct($localeProvider, $propertyAccessor, $formRegistry);
    }

    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return $field->getFieldFqcn() === AttachmentField::class;
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $entityFqcn = parent::supports($field, $entityDto) ?
            $this->getTranslationFqcn($entityDto) : $entityDto->getFqcn();

        $field->setFormTypeOption(
            AdminAttachmentType::CONTEXT,
            (string) (new AttachmentAssociationContextDto(
                $entityFqcn,
                $field->getProperty(),
            )),
        );
    }
}
