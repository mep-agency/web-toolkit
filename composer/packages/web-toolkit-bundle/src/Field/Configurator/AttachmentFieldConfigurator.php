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
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Mep\WebToolkitBundle\Field\AttachmentField;
use Mep\WebToolkitBundle\Form\AdminAttachmentType;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentFieldConfigurator implements FieldConfiguratorInterface
{
    public function __construct(
        private AdminContextProvider $adminContextProvider,
    ) {}

    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return $field->getFieldFqcn() === AttachmentField::class;
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $field->setFormTypeOptionIfNotSet(
            AdminAttachmentType::CRUD_CONTROLER_FQCN,
            $this->adminContextProvider
                ?->getContext()
                ?->getCrud()
                ?->getControllerFqcn(),
        );
    }
}
