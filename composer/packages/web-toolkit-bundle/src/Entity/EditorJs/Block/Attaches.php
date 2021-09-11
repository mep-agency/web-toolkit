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
 * TODO: Implement attaches block (EditorJs).
 *
 * @final You should not extend this class.
 *
 * @see https://github.com/editor-js/attaches
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
//#[ORM\Entity]
//#[ORM\Table(name: 'mwt_editor_js_attaches')]
class Attaches extends Block
{
    public function __construct(string $id,)
    {
        parent::__construct($id);
    }

    protected function getData(): array
    {
        return [];
    }
}
