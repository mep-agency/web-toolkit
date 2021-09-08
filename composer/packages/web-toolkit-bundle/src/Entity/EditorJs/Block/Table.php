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
use Symfony\Component\Validator\Constraints as Assert;

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
    public function __construct(
        string $id,

        #[ORM\Column(type: 'boolean')]
        private bool $withHeadings,

        /**
         * TODO: Converto this to attributes in Symfony 5.4
         *
         * @Assert\All({
         *     @Assert\All({
         *         @Assert\Type(type="string", message="Table cells must contain string values."),
         *     }),
         * })
         */
        #[ORM\Column(type: 'json')]
        private array $content,
    ) {
        parent::__construct($id);
    }

    public function getWithHeadings(): bool
    {
        return $this->withHeadings;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    protected function getData(): array
    {
        return [
            'withHeadings' => $this->withHeadings,
            'content' => $this->content,
        ];
    }
}
