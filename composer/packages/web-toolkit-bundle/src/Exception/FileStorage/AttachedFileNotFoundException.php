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

namespace Mep\WebToolkitBundle\Exception\FileStorage;

use Mep\WebToolkitBundle\Entity\Attachment;
use RuntimeException;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachedFileNotFoundException extends RuntimeException
{
    public function __construct(Attachment $attachment)
    {
        parent::__construct(sprintf('File attached to "%s" could not be found.', $attachment->getId()));
    }
}
