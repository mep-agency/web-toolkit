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
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
class Attachment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private Uuid $id;

    /**
     * @internal Attachment instances should be created by a FileStorageDriverInterface only.
     */
    public function __construct(
        /**
         * @ORM\Column(type="string", length=255)
         */
        private string $fileName,
        /**
         * @ORM\Column(type="string", length=255)
         */
        private string $mimeType,
        /**
         * @ORM\Column(type="integer")
         */
        private int $fileSize,
        /**
         * @ORM\Column(type="json")
         *
         * @var array<string, mixed>
         */
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
     * @return array<string, mixed>
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
        return $this->metadata[$key];
    }

    /**
     * Sets a single metadata value by key.
     *
     * @param mixed $value
     */
    public function set(string $key, $value): self
    {
        $this->metadata[$key] = $value;

        return $this;
    }
}
