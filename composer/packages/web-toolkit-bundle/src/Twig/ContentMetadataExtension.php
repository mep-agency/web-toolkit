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

use Mep\WebToolkitBundle\Service\ContentMetadataManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class ContentMetadataExtension extends AbstractExtension
{
    public function __construct(
        private readonly ContentMetadataManager $contentMetadataManager,
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('content_metadata', function (): string {
                return $this->getContentMetadata();
            }, [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function getContentMetadata(): string
    {
        return '<meta name="description" content="'.$this->contentMetadataManager->getContentDescription().'">
<meta name="og:title" content="'.$this->contentMetadataManager->getTitle().'">
<meta name="og:image" content="'.$this->contentMetadataManager->getImage().'">';
    }
}
