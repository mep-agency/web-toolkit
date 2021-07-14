<?php

declare(strict_types=1);

namespace Mep\WebToolkitBundle\FileStorage;

use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;
use Mep\WebToolkitBundle\Contract\FileStorage\FileStorageDriverInterface;
use Mep\WebToolkitBundle\Entity\Attachment;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class LocalFileStorageDriver implements FileStorageDriverInterface
{
    private Filesystem $filesystem;

    public function __construct(
        private string $storagePath,
        private string $publicUrlPrefix,
        private EntityManagerInterface $entityManager,
    ) {
        $this->filesystem = new Filesystem();
    }

    public function store(File $file, array $metadata = []): Attachment
    {
        if (! $filePath = $file->getRealPath()) {
            throw new FileNotFoundException(
                null,
                0,
                null,
                $file->getPathname()
            );
        }

        $attachment = new Attachment(
            $file->getFilename(),
            $file->getMimeType() ?? 'application/octet-stream',
            $file->getSize(),
            $metadata,
        );

        // Copy new file to storage
        $this->filesystem->copy($filePath, $this->buildFilePath($attachment));

        $this->entityManager->persist($attachment);
        $this->entityManager->flush();

        return $attachment;
    }

    public function remove(Attachment $attachment): void
    {
        $file = new File($this->buildFilePath($attachment));

        $this->entityManager->remove($attachment);
        $this->entityManager->flush();

        // Remove the parent folder in order to avoid leaving it empty.
        $this->filesystem->remove($file->getPath());
    }

    #[Pure]
    public function getPublicUrl(Attachment $attachment): string
    {
        return $this->publicUrlPrefix . '/' . $attachment->getId() . '/' . $attachment->getFileName();
    }

    #[Pure]
    private function buildFilePath(Attachment $attachment): string
    {
        return $this->storagePath . '/' . $attachment->getId() . '/' . $attachment->getFileName();
    }
}
