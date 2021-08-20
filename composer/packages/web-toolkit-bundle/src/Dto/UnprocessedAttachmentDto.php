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

namespace Mep\WebToolkitBundle\Dto;

use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Validator\AssociativeArrayOfScalarValues;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class UnprocessedAttachmentDto
{
    #[NotNull]
    #[NotBlank]
    #[Length(max: 255)]
    public string $fileName;

    #[NotNull]
    #[NotBlank]
    #[Length(max: 255)]
    public string $mimeType;

    #[PositiveOrZero]
    public int $fileSize;

    public function __construct(
        #[NotNull]
        public File $file,

        /**
         * @var array<string, scalar>
         */
        #[AssociativeArrayOfScalarValues]
        public array $metadata = [],
    ) {
        $this->fileName = $file instanceof UploadedFile ? $file->getClientOriginalName() : $file->getFilename();
        $this->mimeType = $file->getMimeType() ?? 'application/octet-stream';
        $this->fileSize = $file->getSize();
    }

    /**
     * @internal Do not use this method directly. Attachment objects should be created using the
     *           FileStorageManager.
     */
    public function createAttachment(): Attachment
    {
        return new Attachment(
            $this->fileName,
            $this->mimeType,
            $this->fileSize,
            $this->metadata,
        );
    }
}
