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
use Symfony\Component\Form\FormRegistryInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class TypeGuesserConfigurator implements FieldConfiguratorInterface
{
    public function __construct(
        private FormRegistryInterface $formRegistry,
    ) {}

    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return property_exists($entityDto->getFqcn(), $field->getProperty());
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $typeGuesser = $this->formRegistry
            ->getTypeGuesser();
        $typeGuess = $typeGuesser->guessType($entityDto->getFqcn(), $field->getProperty());
        $options = $field->getFormTypeOptions();

        // Merge options with guessed options
        if ($typeGuess !== null && $typeGuess->getType() === $field->getFormType()) {
            $options = array_merge($typeGuess->getOptions(), $field->getFormTypeOptions());
        }

        // Set required based on guessed value
        if ($field->getFormTypeOption('required') === null) {
            $requiredGuess = $typeGuesser->guessRequired($entityDto->getFqcn(), $field->getProperty());

            $options = array_merge(['required' => $requiredGuess?->getValue()], $options);
        }

        // Set pattern based on guessed value
        $patternGuess = $typeGuesser->guessPattern($entityDto->getFqcn(), $field->getProperty());
        if ($patternGuess !== null) {
            $options = array_replace_recursive(['attr' => ['pattern' => $patternGuess->getValue()]], $options);
        }

        // Set maxlength based on guessed value
        $maxLengthGuess = $typeGuesser->guessMaxLength($entityDto->getFqcn(), $field->getProperty());
        if ($maxLengthGuess !== null) {
            $options = array_replace_recursive(['attr' => ['maxlength' => $maxLengthGuess->getValue()]], $options);
        }

        $field->setFormTypeOptions($options);
    }
}