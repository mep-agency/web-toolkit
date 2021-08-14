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

use Mep\WebToolkitBundle\Mail\TemplateIdentifier\TwigTemplate;
use Mep\WebToolkitBundle\Mail\TemplateProvider\TwigTemplateProvider;
use RuntimeException;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class MissingRequiredTwigTemplateFileException extends RuntimeException
{
    public function __construct(TwigTemplate $twigTemplate)
    {
        parent::__construct('Expected at least one of "' . TwigTemplateProvider::TEXT_TEMPLATE_NAME . '" or "' . TwigTemplateProvider::HTML_TEMPLATE_NAME . '" in: "' . $twigTemplate->getTemplatesFolder() . '"');
    }
}
