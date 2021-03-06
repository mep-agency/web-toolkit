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
use Mep\WebToolkitBundle\Contract\FileStorage\DriverInterface;
use Mep\WebToolkitBundle\Entity\Attachment;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentLifecycleEventListener
{
    public function __construct(
        private readonly DriverInterface $fileStorageDriver,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function validate(Attachment $attachment, LifecycleEventArgs $lifecycleEventArgs): void
    {
        $constraintViolationList = $this->validator->validate($attachment);

        if ($constraintViolationList->count() > 0) {
            throw new ValidationFailedException($attachment, $constraintViolationList);
        }
    }

    public function initializeAttachmentProxy(Attachment $attachment, LifecycleEventArgs $lifecycleEventArgs): void
    {
        // Ensure that the "postRemove" EventListener doesn't receive an uninitialized proxy
        $lifecycleEventArgs->getObjectManager()
            ->initializeObject($attachment)
        ;
    }

    public function removeAttachedFile(Attachment $attachment, LifecycleEventArgs $lifecycleEventArgs): void
    {
        // Attachment objects may be orphan (the associated file doesn't exist)
        if ($this->fileStorageDriver->attachedFileExists($attachment)) {
            $this->fileStorageDriver->removeAttachedFile($attachment);
        }
    }
}
