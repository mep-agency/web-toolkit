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
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class LocalFileStorageDriver implements FileStorageDriverInterface
{
    private Filesystem $filesystem;

    public function __construct(
        private string $storagePath,
        private string $publicUrlPathPrefix,
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private ?string $publicUrlPrefix = null,
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
        return $this->getPublicUrlPrefix() . $this->publicUrlPathPrefix . '/' . $attachment->getId() . '/' . $attachment->getFileName();
    }

    private function getPublicUrlPrefix(): string
    {
        if ($this->publicUrlPrefix === null) {
            // No public URL prefix was explicitly set, try guessing it from the current request
            return $this->requestStack->getCurrentRequest()?->getSchemeAndHttpHost() ?? '';
        }

        return $this->publicUrlPrefix;
    }

    #[Pure]
    private function buildFilePath(Attachment $attachment): string
    {
        return $this->storagePath . '/' . $attachment->getId() . '/' . $attachment->getFileName();
    }
}
