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

namespace Mep\WebToolkitBundle\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Mep\WebToolkitBundle\Contract\FileStorage\FileStorageDriverInterface;
use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Exception\FileStorage\AttachedFileNotFoundException;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentRemoveEventListener
{
    public function __construct(
        private FileStorageDriverInterface $fileStorageDriver
    ) {}

    public function removeAttachedFile(Attachment $attachment, LifecycleEventArgs $args): void
    {
        try {
            $this->fileStorageDriver->removeAttachedFile($attachment);
        } catch (AttachedFileNotFoundException $e) {
            // The attached file has already been removed, skip this...
        }
    }
}
