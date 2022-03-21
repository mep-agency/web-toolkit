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

namespace Mep\WebToolkitBundle\Mail\TemplateIdentifier;

use Mep\WebToolkitBundle\Contract\Mail\TemplateIdentifierInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class TwigTemplate implements TemplateIdentifierInterface
{
    public function __construct(
        private readonly string $templatesFolder,
    ) {
    }

    public function getTemplatesFolder(): string
    {
        return $this->templatesFolder;
    }
}
