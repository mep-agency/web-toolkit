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
use RuntimeException;
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
    ) {
    }

    public function supports(FieldDto $fieldDto, EntityDto $entityDto): bool
    {
        $entityFqcn = $this->getTranslatableFqcn($entityDto);

        return null !== $entityFqcn &&
            (! property_exists($entityFqcn, $fieldDto->getProperty())
            && property_exists($this->getTranslationFqcn($entityDto), $fieldDto->getProperty()));
    }

    /**
     * @return null|class-string<TranslatableInterface>
     */
    protected function getTranslatableFqcn(EntityDto $entityDto): ?string
    {
        $entityFqcn = $entityDto->getFqcn();

        if (is_a($entityFqcn, TranslatableInterface::class, true)) {
            return $entityFqcn;
        }

        return null;
    }

    /**
     * @return class-string<TranslationInterface>
     */
    protected function getTranslationFqcn(EntityDto $entityDto): string
    {
        $translatableFqcn = $this->getTranslatableFqcn($entityDto);

        if (null === $translatableFqcn) {
            throw new RuntimeException('This class is not translatable: '.$entityDto->getFqcn());
        }

        $translationEntityClass = $translatableFqcn::getTranslationEntityClass();

        if (is_a($translationEntityClass, TranslationInterface::class, true)) {
            return $translationEntityClass;
        }

        throw new RuntimeException('Invalid translation class: '.$translationEntityClass);
    }

    protected function getFieldPropertyPath(FieldDto $fieldDto, EntityDto $entityDto): string
    {
        $currentLocale = $this->localeProvider->provideCurrentLocale();

        if (null === $currentLocale) {
            throw new RuntimeException('Cannot get current locale.');
        }

        /** @var TranslatableInterface $instance */
        $instance = $entityDto->getInstance();
        $isNew = ! $instance->getTranslations()
            ->containsKey($currentLocale)
        ;
        $currentLocale = $this->localeProvider->provideCurrentLocale();

        return ($isNew ? 'newTranslations[' : 'translations[').$currentLocale.'].'.$fieldDto->getProperty();
    }
}
