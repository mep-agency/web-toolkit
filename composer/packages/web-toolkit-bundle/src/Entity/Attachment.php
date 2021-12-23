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

namespace Mep\WebToolkitBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mep\WebToolkitBundle\FileStorage\GarbageCollector\AssociationContextGarbageCollector;
use Mep\WebToolkitBundle\Validator\AssociativeArrayOfScalarValues;
use Stringable;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

/**
 * @final You should not extend this class.
 *
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_attachment')]
class Attachment implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    /**
     * The attachment context can be used to store data in order to help garbage collectors finding unused attachments.
     *
     * @see AssociationContextGarbageCollector
     *
     * @internal attachment instances should be created by the FileStorageManager only
     *
     * @param array<string, scalar> $metadata
     */
    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 255)]
        #[NotNull]
        #[NotBlank]
        #[Length(max: 255)]
        private string $fileName,
        #[ORM\Column(type: Types::STRING, length: 255)]
        #[NotNull]
        #[NotBlank]
        #[Length(max: 255)]
        private string $mimeType,
        #[ORM\Column(type: Types::INTEGER)]
        #[PositiveOrZero]
        private int $fileSize,
        #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
        #[Length(max: 255)]
        private ?string $context = null,
        #[ORM\Column(type: Types::JSON)]
        #[AssociativeArrayOfScalarValues]
        private array $metadata = [],
    ) {
        $this->id = Uuid::v6();
    }

    public function __toString(): string
    {
        return $this->getId()
            ->toRfc4122()
        ;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * @return array<string, scalar>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Gets a single metadata value by key.
     */
    public function get(string $key): string|int|bool|null|float
    {
        return $this->metadata[$key] ?? null;
    }

    /**
     * Sets a single metadata value by key.
     *
     * @param scalar $value
     */
    public function set(string $key, $value): self
    {
        $this->metadata[$key] = $value;

        return $this;
    }
}
