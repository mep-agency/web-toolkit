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

use RuntimeException;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentNotFoundException extends RuntimeException
{
    /**
     * @param mixed $uuid
     */
    public function __construct($uuid)
    {
        parent::__construct('No attachment found for the given UUID: '.$uuid);
    }
}
