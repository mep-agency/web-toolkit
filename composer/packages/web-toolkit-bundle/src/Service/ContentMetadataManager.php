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

namespace Mep\WebToolkitBundle\Service;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class ContentMetadataManager
{
    public function __construct(
        private string $pageTitlePrefix,
        private string $pageTitleSuffix,
        private string $contentTitle,
        private string $contentDescription,
        private string $image,
    ) {
    }

    public function getTitle(): string
    {
        return $this->pageTitlePrefix.'_'.$this->contentTitle.'_'.$this->pageTitleSuffix;
    }

    public function setPageTitlePrefix(string $pageTitlePrefix): void
    {
        $this->pageTitlePrefix = $pageTitlePrefix;
    }

    public function setPageTitleSuffix(string $pageTitleSuffix): void
    {
        $this->pageTitleSuffix = $pageTitleSuffix;
    }

    public function setContentTitle(string $contentTitle): void
    {
        $this->contentTitle = $contentTitle;
    }

    public function getContentDescription(): string
    {
        return $this->contentDescription;
    }

    public function setContentDescription(string $contentDescription): void
    {
        $this->contentDescription = $contentDescription;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }
}
