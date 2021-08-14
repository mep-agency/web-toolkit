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
use EasyCorp\Bundle\EasyAdminBundle\Field\Configurator\BooleanConfigurator;
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Mep\WebToolkitBundle\Contract\Field\Configurator\AbstractTranslatableFieldConfigurator;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
final class TranslatableBooleanConfigurator extends AbstractTranslatableFieldConfigurator
{
    public function __construct(
        protected LocaleProviderInterface $localeProvider,
        protected PropertyAccessorInterface $propertyAccessor,
        protected FormRegistryInterface $formRegistry,
        protected BooleanConfigurator $booleanConfigurator,
    ) {
        parent::__construct($this->localeProvider, $this->propertyAccessor, $this->formRegistry);
    }

    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return $this->booleanConfigurator->supports($field, $entityDto) && parent::supports($field, $entityDto);
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $property = $field->getProperty();

        $field->setProperty($field->getFormTypeOption('property_path'));
        $this->booleanConfigurator->configure($field, $entityDto, $context);
        $field->setProperty($property);
    }
}
