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
 * @see https://github.com/editor-js/quote
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_quote')]
class Quote extends Block
{
    public function __construct(
        string $id,
        #[ORM\Column(type: 'text')]
        private string $text,
        #[ORM\Column(type: 'text')]
        private string $caption,
        #[ORM\Column(type: 'string', length: 10)]
        private string $alignment,
    ) {
        parent::__construct($id);
    }

    public function __toString(): string
    {
        $plainTextTokens = [];

        if (! empty($this->text)) {
            $plainTextTokens[] = $this->text;
        }

        if (! empty($this->caption)) {
            $plainTextTokens[] = $this->caption;
        }

        return implode(PHP_EOL, $plainTextTokens);
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function getAlignment(): string
    {
        return $this->alignment;
    }

    /**
     * @return array<string, string>
     */
    protected function getData(): array
    {
        return [
            'text' => $this->text,
            'caption' => $this->caption,
            'alignment' => $this->alignment,
        ];
    }
}
