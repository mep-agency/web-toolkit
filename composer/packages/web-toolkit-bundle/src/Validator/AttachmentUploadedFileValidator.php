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

use Mep\WebToolkitBundle\Dto\UnprocessedAttachmentDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;

/**
 * @internal Do not use this validator directly.
 *
 * This validator is meant to be used on new uploaded files to ensure that they match the
 * requirements. Metadata is passed "as is" from the constraint since it can't be validated at
 * this stage.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentUploadedFileValidator extends AttachmentFileValidator
{
    /**
     * @param null|string|UploadedFile $file
     * @param AttachmentUploadedFile   $constraint
     */
    public function validate($file, Constraint $constraint)
    {
        if (null === $file || '' === $file) {
            return;
        }

        if (! ($file instanceof UploadedFile)) {
            $this->context->buildViolation('mep_web_toolkit.validators.admin_attachment_upload_type.invalid_value_type')
                ->addViolation()
            ;
        }

        $unprocessedAttachment = new UnprocessedAttachmentDto($file, null, $constraint->metadata);

        parent::validate($unprocessedAttachment->createAttachment(), $constraint);
    }
}
