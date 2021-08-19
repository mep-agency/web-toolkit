<?php

declare(strict_types=1);

namespace Mep\WebToolkitBundle\FileStorage\Processor;

use Mep\WebToolkitBundle\Contract\FileStorage\FileStorageProcessorInterface;
use Mep\WebToolkitBundle\Dto\UnprocessedAttachmentDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Supports UploadedFile objects by using the original filename instead of the temporary one.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
final class UploadedFileProcessor implements FileStorageProcessorInterface
{
    public function supports(UnprocessedAttachmentDto $attachment): bool
    {
        return $attachment->file instanceof UploadedFile;
    }

    public function run(UnprocessedAttachmentDto $attachment, array $processorsOptions): UnprocessedAttachmentDto
    {
        /** @var UploadedFile $file */
        $file = $attachment->file;

        $attachment->fileName = $file->getClientOriginalName();

        if (isset($processorsOptions['do_stuff']) && $processorsOptions['do_stuff'] === true) {
            unset($processorsOptions['do_stuff']);
            $attachment->metadata['stuff_done'] = true;
        }

        return $attachment;
    }
}
