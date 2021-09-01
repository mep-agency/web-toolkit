<?php

namespace Mep\WebToolkitBundle\Form\TypeGuesser;

use Mep\WebToolkitBundle\Form\AdminEditorJsType;
use Mep\WebToolkitBundle\Validator\EditorJs;
use ReflectionProperty;
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
        if ($reflectionProperty->getType()?->getName() !== 'array') {
            return null;
        }

        /** @var ?EditorJs $editorJsAttribute */
        $editorJsAttribute = ($reflectionProperty->getAttributes(EditorJs::class)[0] ?? null)
            ?->newInstance();

        if ($editorJsAttribute === null) {
            return null;
        }

        return new TypeGuess(
            AdminEditorJsType::class,
            [
                AdminEditorJsType::ENABLED_TOOLS => $editorJsAttribute->enabledTools,
                AdminEditorJsType::TOOLS_OPTIONS => $editorJsAttribute->options,
            ],
            Guess::HIGH_CONFIDENCE
        );
    }

    public function guessRequired(string $class, string $property)
    {}

    public function guessMaxLength(string $class, string $property)
    {}

    public function guessPattern(string $class, string $property)
    {}
}
