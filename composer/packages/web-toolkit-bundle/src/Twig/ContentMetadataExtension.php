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

namespace Mep\WebToolkitBundle\Twig;

use LogicException;
use Mep\WebToolkitBundle\Service\ContentMetadataManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class ContentMetadataExtension extends AbstractExtension
{
    public function __construct(
        private readonly ContentMetadataManager $contentMetadataManager,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('content_metadata', function (Environment $env, bool $isSuccessResponse): string {
                return $this->getContentMetadata($env, $isSuccessResponse);
            }, [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function getContentMetadata(Environment $environment, bool $isSuccessResponse): string
    {
        $metadata = '<title>'.$this->contentMetadataManager->getTitle().'</title>
<meta name="description" content="'.twig_escape_filter(
            $environment,
            $this->contentMetadataManager->getContentDescription(),
            'html_attr',
        ).'">';

        if ($isSuccessResponse) {
            $url = $this->requestStack->getMainRequest()?->getUri() ??
                throw new LogicException('URL not valid.');

            $metadata .= '
<meta name="og:title" content="'.twig_escape_filter(
                $environment,
                $this->contentMetadataManager->getTitle(),
                'html_attr',
            ).'">
<meta name="og:type" content="'.twig_escape_filter(
                $environment,
                $this->contentMetadataManager->getType(),
                'html_attr',
            ).'">
<meta name="og:image" content="'.twig_escape_filter(
                $environment,
                $this->contentMetadataManager->getImage(),
                'html_attr',
            ).'">
<meta name="og:url" content="'.twig_escape_filter($environment, $url, 'html_attr',).'">';
        }

        return $metadata;
    }
}
