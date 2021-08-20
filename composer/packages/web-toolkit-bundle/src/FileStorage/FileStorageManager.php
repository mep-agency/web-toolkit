<?php

declare(strict_types=1);

namespace Mep\WebToolkitBundle\FileStorage;

use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;
use Mep\WebToolkitBundle\Contract\FileStorage\FileStorageDriverInterface;
use Mep\WebToolkitBundle\Contract\FileStorage\FileStorageProcessorInterface;
use Mep\WebToolkitBundle\Dto\UnprocessedAttachmentDto;
use Mep\WebToolkitBundle\Entity\Attachment;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class FileStorageManager
{
    /**
     * @param iterable<FileStorageProcessorInterface> $processors
     */
    public function __construct(
        private FileStorageDriverInterface $fileStorageDriver,
        private EntityManagerInterface $entityManager,
        private iterable $processors,
    ) {}

    /**
     * @param array<string, scalar> $metadata
     * @param array<string, scalar> $processorsOptions
     */
    public function store(File $file, array $metadata = [], array $processorsOptions = []): Attachment
    {
        if (! $file->getRealPath()) {
            throw new FileNotFoundException(
                null,
                0,
                null,
                $file->getPathname()
            );
        }

        $unprocessedAttachment = new UnprocessedAttachmentDto($file, $metadata);

        // Run processors
        foreach ($this->processors as $processor) {
            if ($processor->supports($unprocessedAttachment)) {
                $unprocessedAttachment = $processor->run($unprocessedAttachment, $processorsOptions);
            }
        }

        $attachment = $unprocessedAttachment->createAttachment();

        $this->fileStorageDriver->store(
            $unprocessedAttachment->file,
            $attachment,
        );

        $this->entityManager->persist($attachment);
        $this->entityManager->flush();

        return $attachment;
    }

    #[Pure]
    public function getPublicUrl(Attachment $attachment): string
    {
        return $this->fileStorageDriver->getPublicUrl($attachment);
    }
}
