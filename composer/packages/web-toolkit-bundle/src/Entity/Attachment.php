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

use Doctrine\ORM\Mapping as ORM;
use Mep\WebToolkitBundle\Validator\AssociativeArrayOfScalarValues;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

/**
 * @final You should not extend this class.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_attachment')]
class Attachment
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    /**
     * @internal Attachment instances should be created by the FileStorageManager only.
     */
    public function __construct(
        #[ORM\Column(type: 'string', length: 255)]
        #[NotNull]
        #[NotBlank]
        #[Length(max: 255)]
        private string $fileName,

        #[ORM\Column(type: 'string', length: 255)]
        #[NotNull]
        #[NotBlank]
        #[Length(max: 255)]
        private string $mimeType,

        #[ORM\Column(type: 'integer')]
        #[PositiveOrZero]
        private int $fileSize,

        /**
         * @var array<string, scalar>
         */
        #[ORM\Column(type: 'json')]
        #[AssociativeArrayOfScalarValues]
        private array $metadata = [],
    ) {
        $this->id = Uuid::v6();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
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
    public function get(string $key): mixed
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

    public function __toString(): string
    {
        return $this->getId()->toRfc4122();
    }
}
