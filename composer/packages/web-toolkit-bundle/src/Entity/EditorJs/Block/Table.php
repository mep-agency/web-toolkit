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
use Mep\WebToolkitBundle\Entity\EditorJs\Block;
use Mep\WebToolkitBundle\Validator\EditorJs\TableContent;

/**
 * @final You should not extend this class.
 *
 * @see https://github.com/editor-js/table
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_table')]
class Table extends Block
{
    /**
     * @param string[][] $content
     */
    public function __construct(
        string $id,
        #[ORM\Column(type: Types::BOOLEAN)]
        private bool $withHeadings,
        #[TableContent]
        #[ORM\Column(type: Types::JSON)]
        private array $content,
    ) {
        parent::__construct($id);
    }

    public function __toString(): string
    {
        $plainTextTokens = [];

        foreach ($this->content as $singleContent) {
            if (! empty($singleContent)) {
                $plainTextTokens[] = implode("\t", $singleContent);
            }
        }

        return strip_tags(implode(PHP_EOL, $plainTextTokens));
    }

    public function getWithHeadings(): bool
    {
        return $this->withHeadings;
    }

    /**
     * @return array<string[]>
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @return array<string, bool|string[][]>
     */
    protected function getData(): array
    {
        return [
            'withHeadings' => $this->withHeadings,
            'content' => $this->content,
        ];
    }
}
