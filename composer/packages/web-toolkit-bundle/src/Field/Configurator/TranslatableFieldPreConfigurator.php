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

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class TranslatableFieldPreConfigurator extends AbstractTranslatableFieldConfigurator
{
    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        // Using type-guessing based on the property of the TranslationInterface class
        $translationFqcn = $this->getTranslationFqcn($entityDto);
        $typeGuesser = $this->formRegistry
            ->getTypeGuesser();
        $typeGuess = $typeGuesser->guessType($translationFqcn, $field->getProperty());
        $options = $field->getFormTypeOptions();

        // Merge options with guessed options
        if ($typeGuess !== null && $typeGuess->getType() === $field->getFormType()) {
            $options = array_merge($typeGuess->getOptions(), $field->getFormTypeOptions());
        }

        // Set required based on guessed value
        if ($field->getFormTypeOption('required') === null) {
            $requiredGuess = $typeGuesser->guessRequired($translationFqcn, $field->getProperty());

            $options = array_merge(['required' => $requiredGuess?->getValue()], $options);
        }

        // Set pattern based on guessed value
        $patternGuess = $typeGuesser->guessPattern($translationFqcn, $field->getProperty());
        if ($patternGuess !== null) {
            $options = array_replace_recursive(['attr' => ['pattern' => $patternGuess->getValue()]], $options);
        }

        // Set maxlength based on guessed value
        $maxLengthGuess = $typeGuesser->guessMaxLength($translationFqcn, $field->getProperty());
        if ($maxLengthGuess !== null) {
            $options = array_replace_recursive(['attr' => ['maxlength' => $maxLengthGuess->getValue()]], $options);
        }

        $field->setFormTypeOptions($options);
    }
}