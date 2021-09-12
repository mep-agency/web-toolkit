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

namespace Mep\WebToolkitBundle\Validator;

use Mep\WebToolkitBundle\Entity\Attachment as AttachmentEntity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @final You should not extend this class.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
class AttachmentFileValidator extends ConstraintValidator
{
    /**
     * @param null|AttachmentEntity|string $attachment
     * @param AttachmentFile               $constraint
     */
    public function validate($attachment, Constraint $constraint): void
    {
        if (null === $attachment || '' === $attachment) {
            return;
        }

        if (! $attachment instanceof AttachmentEntity) {
            $this->context->buildViolation('Invalid attachment value.')
                ->addViolation()
            ;

            return;
        }

        if ($constraint->maxSize > 0 && $attachment->getFileSize() > $constraint->maxSize) {
            $this->context->buildViolation('mep_web_toolkit.validators.admin_attachment_upload_type.max_size_exceeded')
                ->setParameter('max_size', (string) $constraint->maxSize)
                ->addViolation()
            ;
        }

        // No value -> no restriction
        $mimeIsValid = count($constraint->allowedMimeTypes) < 1;

        foreach ($constraint->allowedMimeTypes as $allowedMimeType) {
            if (1 === preg_match($allowedMimeType, $attachment->getMimeType())) {
                $mimeIsValid = true;
            }
        }

        if (! $mimeIsValid) {
            $this->context->buildViolation('mep_web_toolkit.validators.admin_attachment_upload_type.invalid_mime_type')
                ->setParameter('mime_type', $attachment->getMimeType())
                ->addViolation()
            ;
        }

        if (
            null !== $constraint->allowedNamePattern &&
            1 !== preg_match($constraint->allowedNamePattern, $attachment->getFileName())
        ) {
            $this->context->buildViolation('mep_web_toolkit.validators.admin_attachment_upload_type.invalid_file_name')
                ->addViolation()
            ;
        }

        foreach ($constraint->metadata as $key => $value) {
            if ($attachment->get($key) !== $value) {
                $this->context->buildViolation(
                    'mep_web_toolkit.validators.admin_attachment_upload_type.invalid_metadata_value',
                )
                    ->setParameter('key', $key)
                    ->setParameter('value', (string) $attachment->get($key))
                    ->addViolation()
                ;
            }
        }
    }
}
