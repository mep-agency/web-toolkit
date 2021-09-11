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

use Mep\WebToolkitBundle\Dto\UnprocessedAttachmentDto;
use Mep\WebToolkitBundle\Exception\FileStorage\InvalidProcessorOptionsException;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
interface ProcessorInterface
{
    /**
     * @throws InvalidProcessorOptionsException
     */
    public function supports(UnprocessedAttachmentDto $attachment): bool;

    public function run(UnprocessedAttachmentDto $attachment): UnprocessedAttachmentDto;
}
