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

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Entity\EditorJs\Block;

/**
 * @final You should not extend this class.
 *
 * @see https://github.com/editor-js/attaches
 *
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_attaches')]
class Attaches extends Block
{
    public function __construct(
        string $id,
        #[ORM\Column(type: Types::TEXT)]
        private string $title,
        #[ORM\ManyToOne(targetEntity: Attachment::class, cascade: ['persist'], fetch: 'EAGER')]
        private Attachment $attachment,
    ) {
        parent::__construct($id);
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAttachment(): Attachment
    {
        return $this->attachment;
    }

    /**
     * @return array<string, Attachment|string>
     */
    protected function getData(): array
    {
        return [
            'title' => $this->title,
            'attachment' => $this->attachment,
        ];
    }
}
