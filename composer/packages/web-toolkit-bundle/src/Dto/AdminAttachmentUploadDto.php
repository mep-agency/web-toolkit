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

namespace Mep\WebToolkitBundle\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @internal Do not use this class directly.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AdminAttachmentUploadDto
{
    #[NotBlank]
    #[NotNull]
    public UploadedFile $file;

    public ?string $context = null;
}
