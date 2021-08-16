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

namespace Mep\WebToolkitBundle\Contract\FileStorage;

use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Exception\FileStorage\AttachedFileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
interface FileStorageDriverInterface
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function store(File $file, array $metadata = []): Attachment;

    /**
     * @internal Don't call this directly. This method is called by a dedicated Doctrine
     *           EventListener when an Attachment entity is deleted.
     *
     * @throws AttachedFileNotFoundException
     */
    public function removeAttachedFile(Attachment $attachment): void;

    public function getPublicUrl(Attachment $attachment): string;
}