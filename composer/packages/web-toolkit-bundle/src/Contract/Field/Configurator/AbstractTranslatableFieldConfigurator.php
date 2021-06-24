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

namespace Mep\WebToolkitBundle\Contract\Field\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
abstract class AbstractTranslatableFieldConfigurator implements FieldConfiguratorInterface
{
    public function __construct(
        protected LocaleProviderInterface $localeProvider,
        protected PropertyAccessorInterface $propertyAccessor,
        protected FormRegistryInterface $formRegistry,
    ) {}

    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        if (! in_array(
            TranslatableInterface::class,
            class_implements($entityFqcn = $this->getTranslatableFqcn($entityDto)),
            true
        )) {
            return false;
        }

        return
            ! property_exists($entityFqcn, $field->getProperty())
            && property_exists($this->getTranslationFqcn($entityDto), $field->getProperty());
    }

    /**
     * @return class-string<TranslatableInterface>
     */
    protected function getTranslatableFqcn(EntityDto $entityDto): string
    {
        return $entityDto->getFqcn();
    }

    /**
     * @return class-string<TranslationInterface>
     */
    protected function getTranslationFqcn(EntityDto $entityDto): string
    {
        return $this->getTranslatableFqcn($entityDto)::getTranslationEntityClass();
    }

    protected function getFieldPropertyPath(FieldDto $field, EntityDto $entityDto): string
    {
        /** @var TranslatableInterface $instance */
        $instance = $entityDto->getInstance();
        $isNew = ! $instance->getTranslations()->containsKey($this->localeProvider->provideCurrentLocale());
        $currentLocale = $this->localeProvider->provideCurrentLocale();

        return ($isNew ? 'newTranslations[' : 'translations[') . $currentLocale . '].' . $field->getProperty();
    }
}
