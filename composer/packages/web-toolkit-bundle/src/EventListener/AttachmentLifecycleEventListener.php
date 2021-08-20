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
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentLifecycleEventListener
{
    public function __construct(
        private FileStorageDriverInterface $fileStorageDriver,
        private ValidatorInterface $validator,
    ) {}

    public function validate(Attachment $attachment, LifecycleEventArgs $args): void
    {
        $validations = $this->validator->validate($attachment);

        if ($validations->count() > 0) {
            throw new ValidationFailedException($attachment, $validations);
        }
    }

    public function removeAttachedFile(Attachment $attachment, LifecycleEventArgs $args): void
    {
        // Attachment objects may be orphan (the associated file doesn't exist)
        if ($this->fileStorageDriver->attachedFileExists($attachment)) {
            $this->fileStorageDriver->removeAttachedFile($attachment);
        }
    }
}
