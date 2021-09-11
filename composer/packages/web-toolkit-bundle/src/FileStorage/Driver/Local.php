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

namespace Mep\WebToolkitBundle\FileStorage\Driver;

use JetBrains\PhpStorm\Pure;
use Mep\WebToolkitBundle\Contract\FileStorage\DriverInterface;
use Mep\WebToolkitBundle\Entity\Attachment;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal do not use this class directly, use the FileStorageManager class instead
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
final class Local implements DriverInterface
{
    private Filesystem $filesystem;

    public function __construct(
        private string $storagePath,
        private string $publicUrlPathPrefix,
        private RequestStack $requestStack,
        private ?string $publicUrlPrefix = null,
    ) {
        $this->filesystem = new Filesystem();
    }

    public function store(File $file, Attachment $attachment): void
    {
        // Copy new file to storage
        $this->filesystem->copy($file->getRealPath(), $this->buildFilePath($attachment));
    }

    #[Pure]
    public function attachedFileExists(Attachment $attachment): bool
    {
        return is_file($this->buildFilePath($attachment));
    }

    public function removeAttachedFile(Attachment $attachment): void
    {
        $file = new File($this->buildFilePath($attachment));

        // Remove the parent folder in order to avoid leaving it empty.
        $this->filesystem->remove($file->getPath());
    }

    public function getPublicUrl(Attachment $attachment): string
    {
        return $this->getPublicUrlPrefix().$this->publicUrlPathPrefix.'/'.$attachment->getId().'/'.$attachment->getFileName();
    }

    private function getPublicUrlPrefix(): string
    {
        if (null === $this->publicUrlPrefix) {
            // No public URL prefix was explicitly set, try guessing it from the current request
            return $this->requestStack->getCurrentRequest()?->getSchemeAndHttpHost() ?? '';
        }

        return $this->publicUrlPrefix;
    }

    #[Pure]
    private function buildFilePath(Attachment $attachment): string
    {
        return $this->storagePath.'/'.$attachment->getId().'/'.$attachment->getFileName();
    }
}