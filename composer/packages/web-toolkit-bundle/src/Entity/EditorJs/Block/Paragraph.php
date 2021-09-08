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

use Mep\WebToolkitBundle\Entity\EditorJs\Block;
use Doctrine\ORM\Mapping as ORM;

/**
 * @final You should not extend this class.
 *
 * @see https://github.com/editor-js/paragraph
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_paragraph')]
class Paragraph extends Block
{
    public function __construct(
        string $id,

        #[ORM\Column(type: 'text')]
        private string $text,
    ) {
        parent::__construct($id);
    }

    public function getText(): string
    {
        return $this->text;
    }

    protected function getData(): array
    {
        return [
            'text' => $this->text,
        ];
    }
}
