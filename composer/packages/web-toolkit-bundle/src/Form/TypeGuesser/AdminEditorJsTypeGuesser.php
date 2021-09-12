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

namespace Mep\WebToolkitBundle\Form\TypeGuesser;

use Mep\WebToolkitBundle\Entity\EditorJs\EditorJsContent;
use Mep\WebToolkitBundle\Form\AdminEditorJsType;
use Mep\WebToolkitBundle\Validator\EditorJs\EditorJs;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AdminEditorJsTypeGuesser implements FormTypeGuesserInterface
{
    public function guessType(string $class, string $property): ?TypeGuess
    {
        $reflectionProperty = new ReflectionProperty($class, $property);
        $reflectionType = $reflectionProperty->getType();

        if (! $reflectionType instanceof ReflectionType) {
            return null;
        }

        if ($reflectionType instanceof ReflectionNamedType && EditorJsContent::class !== $reflectionType->getName()) {
            return null;
        }

        if ($reflectionType instanceof ReflectionUnionType) {
            $isValid = false;

            foreach ($reflectionType->getTypes() as $type) {
                if (EditorJsContent::class === $type->getName()) {
                    $isValid = true;

                    break;
                }
            }

            if (! $isValid) {
                return null;
            }
        }

        $editorJsAttribute = ($reflectionProperty->getAttributes(EditorJs::class)[0] ?? null)
            ?->newInstance()
        ;

        return new TypeGuess(
            AdminEditorJsType::class,
            $editorJsAttribute instanceof EditorJs ? [
                AdminEditorJsType::ENABLED_TOOLS => $editorJsAttribute->enabledTools,
                AdminEditorJsType::TOOLS_OPTIONS => $editorJsAttribute->options,
            ] : [],
            Guess::VERY_HIGH_CONFIDENCE,
        );
    }

    public function guessRequired(string $class, string $property)
    {
        return null;
    }

    public function guessMaxLength(string $class, string $property)
    {
        return null;
    }

    public function guessPattern(string $class, string $property)
    {
        return null;
    }
}
