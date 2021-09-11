<?php

namespace Mep\WebToolkitBundle\Form\TypeGuesser;

use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Form\AdminAttachmentType;
use Mep\WebToolkitBundle\Validator\AttachmentFile;
use ReflectionProperty;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AdminAttachmentTypeGuesser implements FormTypeGuesserInterface
{
    public function guessType(string $class, string $property): ?TypeGuess
    {
        $reflectionProperty = new ReflectionProperty($class, $property);

        if ($reflectionProperty->getType()?->getName() !== Attachment::class) {
            return null;
        }

        /** @var ?AttachmentFile $validAttachmentAttribute */
        $validAttachmentAttribute = ($reflectionProperty->getAttributes(AttachmentFile::class)[0] ?? null)
            ?->newInstance();

        return new TypeGuess(
            AdminAttachmentType::class,
            $validAttachmentAttribute === null ? [] :
                [
                    AdminAttachmentType::MAX_SIZE => $validAttachmentAttribute->maxSize,
                    AdminAttachmentType::ALLOWED_MIME_TYPES => $validAttachmentAttribute->allowedMimeTypes,
                    AdminAttachmentType::ALLOWED_NAME_PATTERN => $validAttachmentAttribute->allowedNamePattern,
                    AdminAttachmentType::METADATA => $validAttachmentAttribute->metadata,
                    AdminAttachmentType::PROCESSORS_OPTIONS => $validAttachmentAttribute->processorsOptions,
                ],
            Guess::VERY_HIGH_CONFIDENCE
        );
    }

    public function guessRequired(string $class, string $property)
    {}

    public function guessMaxLength(string $class, string $property)
    {}

    public function guessPattern(string $class, string $property)
    {}
}
