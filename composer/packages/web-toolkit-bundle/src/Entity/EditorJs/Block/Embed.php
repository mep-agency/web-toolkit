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
use Mep\WebToolkitBundle\Entity\EditorJs\Block;

/**
 * @final You should not extend this class.
 *
 * @see https://github.com/editor-js/embed
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_embed')]
class Embed extends Block
{
    public function __construct(
        string $id,
        #[ORM\Column(type: 'string', length: 32)]
        private string $service,
        #[ORM\Column(type: 'string', length: 255)]
        private string $source,
        #[ORM\Column(type: 'string', length: 255)]
        private string $embed,
        #[ORM\Column(type: 'smallint')]
        private int $width,
        #[ORM\Column(type: 'smallint')]
        private int $height,
        #[ORM\Column(type: 'text')]
        private string $caption,
    ) {
        parent::__construct($id);
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getEmbed(): string
    {
        return $this->embed;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }

    protected function getData(): array
    {
        return [
            'service' => $this->service,
            'source' => $this->source,
            'embed' => $this->embed,
            'width' => $this->width,
            'height' => $this->height,
            'caption' => $this->caption,
        ];
    }
}
