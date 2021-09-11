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

namespace Mep\WebToolkitBundle\Exception\Mail;

use Mep\WebToolkitBundle\Contract\Mail\TemplateIdentifierInterface;
use RuntimeException;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class UnsupportedTemplateIdentifierException extends RuntimeException
{
    public function __construct(TemplateIdentifierInterface $templateIdentifier)
    {
        parent::__construct('Trying to render an unsupported template: '.get_class(
            $templateIdentifier,
        ).' (no provider found)');
    }
}
