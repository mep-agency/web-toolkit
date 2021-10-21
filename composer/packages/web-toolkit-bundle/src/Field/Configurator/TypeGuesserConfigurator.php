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
use Symfony\Component\Form\FormTypeGuesserInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class TypeGuesserConfigurator implements FieldConfiguratorInterface
{
    public function __construct(
        private FormRegistryInterface $formRegistry,
    ) {
    }

    public function supports(FieldDto $fieldDto, EntityDto $entityDto): bool
    {
        return property_exists($entityDto->getFqcn(), $fieldDto->getProperty());
    }

    public function configure(FieldDto $fieldDto, EntityDto $entityDto, AdminContext $adminContext): void
    {
        $formTypeGuesser = $this->formRegistry
            ->getTypeGuesser()
        ;

        if (! $formTypeGuesser instanceof FormTypeGuesserInterface) {
            return;
        }

        $typeGuess = $formTypeGuesser->guessType($entityDto->getFqcn(), $fieldDto->getProperty());
        $options = $fieldDto->getFormTypeOptions();

        // Merge options with guessed options
        if (null !== $typeGuess && $typeGuess->getType() === $fieldDto->getFormType()) {
            $options = array_merge($typeGuess->getOptions(), $fieldDto->getFormTypeOptions());
        }

        // Set required based on guessed value
        if (null === $fieldDto->getFormTypeOption('required')) {
            $valueGuess = $formTypeGuesser->guessRequired($entityDto->getFqcn(), $fieldDto->getProperty());

            $options = array_merge([
                'required' => $valueGuess?->getValue(),
            ], $options);
        }

        // Set pattern based on guessed value
        $patternGuess = $formTypeGuesser->guessPattern($entityDto->getFqcn(), $fieldDto->getProperty());
        if (null !== $patternGuess?->getValue()) {
            $options = array_replace_recursive([
                'attr' => [
                    'pattern' => $patternGuess->getValue(),
                ],
            ], $options);
        }

        // Set maxlength based on guessed value
        $maxLengthGuess = $formTypeGuesser->guessMaxLength($entityDto->getFqcn(), $fieldDto->getProperty());
        if (null !== $maxLengthGuess?->getValue()) {
            $options = array_replace_recursive([
                'attr' => [
                    'maxlength' => $maxLengthGuess->getValue(),
                ],
            ], $options);
        }

        $fieldDto->setFormTypeOptions($options);
    }
}
