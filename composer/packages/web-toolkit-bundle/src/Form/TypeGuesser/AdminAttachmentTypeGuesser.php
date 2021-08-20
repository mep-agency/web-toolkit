<?php

namespace Mep\WebToolkitBundle\Form\TypeGuesser;

use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Form\AdminAttachmentType;
use Mep\WebToolkitBundle\Validator\ValidAttachment;
use ReflectionProperty;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class AdminAttachmentTypeGuesser implements FormTypeGuesserInterface
{
    public function guessType(string $class, string $property): ?TypeGuess
    {
        $reflectionProperty = new ReflectionProperty($class, $property);

        if ($reflectionProperty->getType()?->getName() !== Attachment::class) {
            return null;
        }

        $validAttachmentAttributes = $reflectionProperty->getAttributes(ValidAttachment::class);
        $attributeArguments = ($validAttachmentAttributes[0] ?? null)?->getArguments();

        return new TypeGuess(
            AdminAttachmentType::class,
            $attributeArguments === null ? [] :
                [
                    AdminAttachmentType::MAX_SIZE => $attributeArguments['maxSize'] ?? 0,
                    AdminAttachmentType::ALLOWED_MIME_TYPES => $attributeArguments['allowedMimeTypes'] ?? [],
                    AdminAttachmentType::ALLOWED_NAME_PATTERN => $attributeArguments['allowedNamePattern'] ?? null,
                    AdminAttachmentType::METADATA => $attributeArguments['metadata'] ?? [],
                    AdminAttachmentType::PROCESSORS_OPTIONS => $attributeArguments['processorsOptions'] ?? [],
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
