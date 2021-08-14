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

namespace Mep\WebToolkitBundle\Mail;

use Mep\WebToolkitBundle\Contract\Mail\TemplateIdentifierInterface;
use Mep\WebToolkitBundle\Contract\Mail\TemplateProviderInterface;
use Mep\WebToolkitBundle\Exception\Mail\UnsupportedTemplateIdentifierException;
use Mep\WebToolkitBundle\Mail\TemplateIdentifier\TwigTemplate;
use Symfony\Component\Mime\Email;
use Twig\Environment;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class TemplateRenderer
{
    /**
     * @param iterable<TemplateProviderInterface> $templateProviders
     */
    public function __construct(private iterable $templateProviders)
    {}

    /**
     * @param array<string, mixed> $parameters
     */
    public function render(TemplateIdentifierInterface $templateIdentifier, array $parameters = []): Email
    {
        foreach ($this->templateProviders as $templateProvider) {
            if ($templateProvider->supports($templateIdentifier)) {
                return $templateProvider->render($templateIdentifier, $parameters);
            }
        }

        throw new UnsupportedTemplateIdentifierException($templateIdentifier);
    }
}
