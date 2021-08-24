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

namespace Mep\WebToolkitBundle\FileStorage\Processor;

use Mep\WebToolkitBundle\Contract\FileStorage\FileStorageProcessorInterface;
use Mep\WebToolkitBundle\Dto\UnprocessedAttachmentDto;
use function Tinify\fromFile as compressFromFile;
use Tinify\Tinify;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
final class TinifyProcessor implements FileStorageProcessorInterface
{
    public function __construct($apiKey)
    {
        Tinify::setKey($apiKey);
    }

    public function supports(UnprocessedAttachmentDto $attachment): bool
    {
        return str_starts_with($attachment->mimeType, 'image/');
    }

    public function run(UnprocessedAttachmentDto $attachment, array $processorsOptions): UnprocessedAttachmentDto
    {
        if ($processorsOptions['compress'] === true) {
            $file = compressFromFile($attachment->file->getRealPath());
            $file->toFile($attachment->file->getRealPath());

            $attachment->fileSize = $attachment->file->getSize();
            $attachment->metadata['tinify'] = true;
        }

        return $attachment;
    }
}
