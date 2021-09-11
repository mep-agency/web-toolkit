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
 * @see https://github.com/editor-js/warning
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_warning')]
class Warning extends Block
{
    public function __construct(
        string $id,
        #[ORM\Column(type: 'string', length: 255)]
        private string $title,
        #[ORM\Column(type: 'text')]
        private string $message,
    ) {
        parent::__construct($id);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array<string, string>
     */
    protected function getData(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
        ];
    }
}
