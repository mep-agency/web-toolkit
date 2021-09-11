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

namespace Mep\WebToolkitBundle\Validator\EditorJs;

use Mep\WebToolkitBundle\Entity\EditorJs\EditorJsContent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class EditorJsNotEmptyValidator extends ConstraintValidator
{
    /**
     * @param null|EditorJsContent|string $editorJsContent
     * @param EditorJsNotEmpty            $constraint
     */
    public function validate($editorJsContent, Constraint $constraint): void
    {
        if (null === $editorJsContent || '' === $editorJsContent) {
            return;
        }

        if (0 === $editorJsContent->getBlocks()->count()) {
            $this->context->buildViolation('This value cannot be empty.')
                ->addViolation()
            ;
        }
    }
}
