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

namespace Mep\WebToolkitBundle\Entity\EditorJs\Block;

use Doctrine\ORM\Mapping as ORM;
use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Entity\EditorJs\Block;

/**
 * @final You should not extend this class.
 *
 * @see https://github.com/editor-js/image
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_image')]
class Image extends Block
{
    public function __construct(
        string $id,
        #[ORM\Column(type: 'text')]
        private string $caption,
        #[ORM\Column(type: 'boolean')]
        private bool $withBorder,
        #[ORM\Column(type: 'boolean')]
        private bool $withBackground,
        #[ORM\Column(type: 'boolean')]
        private bool $stretched,
        #[ORM\ManyToOne(targetEntity: Attachment::class, cascade: ['persist'], fetch: 'EAGER')]
        private Attachment $attachment,
    ) {
        parent::__construct($id);
    }

    public function __toString(): string
    {
        return $this->caption;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function isWithBorder(): bool
    {
        return $this->withBorder;
    }

    public function isWithBackground(): bool
    {
        return $this->withBackground;
    }

    public function isStretched(): bool
    {
        return $this->stretched;
    }

    public function getAttachment(): Attachment
    {
        return $this->attachment;
    }

    /**
     * @return array<string, Attachment|bool|string>
     */
    protected function getData(): array
    {
        return [
            'caption' => $this->caption,
            'withBorder' => $this->withBorder,
            'withBackground' => $this->withBackground,
            'stretched' => $this->stretched,
            'attachment' => $this->attachment,
        ];
    }
}
