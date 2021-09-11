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

namespace Mep\WebToolkitBundle\Mail\TemplateProvider;

use Mep\WebToolkitBundle\Contract\Mail\TemplateIdentifierInterface;
use Mep\WebToolkitBundle\Contract\Mail\TemplateProviderInterface;
use Mep\WebToolkitBundle\Mail\TemplateIdentifier\DummyTemplate;
use Symfony\Component\Mime\Email;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class DummyTemplateProvider implements TemplateProviderInterface
{
    public function supports(TemplateIdentifierInterface $templateIdentifier): bool
    {
        return $templateIdentifier instanceof DummyTemplate;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function render(TemplateIdentifierInterface $templateIdentifier, array $parameters = []): Email
    {
        $html = '';

        foreach ($parameters as $key => $value) {
            if (is_object($value) && ! method_exists($value, '__toString')) {
                $value = '<pre>'.print_r($value, true).'</pre>';
            }

            $html .= PHP_EOL.'<li><strong>'.$key.':</strong> '.$value.'</li>';
        }

        $html = '<h1>Available parameters:</h1>'.PHP_EOL.'<ul>'.$html.PHP_EOL.'</ul>';

        $email = new Email();
        $email->subject('This is a testing message rendered from a dummy template');
        $email->text(html_entity_decode(strip_tags($html)));
        $email->html($html);

        return $email;
    }
}
