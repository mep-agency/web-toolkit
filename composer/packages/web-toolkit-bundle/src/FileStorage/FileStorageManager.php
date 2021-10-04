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

namespace Mep\WebToolkitBundle\FileStorage;

use Doctrine\ORM\EntityManagerInterface;
use Mep\WebToolkitBundle\Contract\FileStorage\DriverInterface;
use Mep\WebToolkitBundle\Contract\FileStorage\ProcessorInterface;
use Mep\WebToolkitBundle\Dto\UnprocessedAttachmentDto;
use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Exception\FileStorage\InvalidProcessorOptionsException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class FileStorageManager
{
    /**
     * @param iterable<ProcessorInterface> $processors
     */
    public function __construct(
        private DriverInterface $fileStorageDriver,
        private EntityManagerInterface $entityManager,
        private iterable $processors,
    ) {
    }

    /**
     * @param array<string, scalar> $metadata
     * @param array<string, scalar> $processorsOptions
     */
    public function store(
        File $file,
        ?string $context = null,
        array $metadata = [],
        array $processorsOptions = [],
    ): Attachment {
        if (! $file->getRealPath()) {
            throw new FileNotFoundException(null, 0, null, $file->getPathname());
        }

        $unprocessedAttachment = new UnprocessedAttachmentDto($file, $context, $metadata, $processorsOptions);

        // Run processors
        foreach ($this->processors as $processor) {
            if ($processor->supports($unprocessedAttachment)) {
                $unprocessedAttachment = $processor->run($unprocessedAttachment);
            }
        }

        if (! empty($unprocessedAttachment->processorsOptions)) {
            throw new InvalidProcessorOptionsException('Processors options are not empty, but all processors have been run. Some configuration may be wrong/missing.');
        }

        $attachment = $unprocessedAttachment->createAttachment();

        $this->fileStorageDriver->store($unprocessedAttachment->file, $attachment);

        $this->entityManager->persist($attachment);
        $this->entityManager->flush();

        return $attachment;
    }

    public function getPublicUrl(Attachment $attachment): string
    {
        return $this->fileStorageDriver->getPublicUrl($attachment);
    }
}
