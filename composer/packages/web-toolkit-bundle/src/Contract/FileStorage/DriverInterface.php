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
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal Do not use this class directly, use the FileStorageManager class instead.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
interface DriverInterface
{
    public function store(File $file, Attachment $attachment): void;

    public function attachedFileExists(Attachment $attachment): bool;

    public function removeAttachedFile(Attachment $attachment): void;

    public function getPublicUrl(Attachment $attachment): string;
}