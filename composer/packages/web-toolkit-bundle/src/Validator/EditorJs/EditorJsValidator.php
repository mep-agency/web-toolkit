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

use Mep\WebToolkitBundle\Entity\EditorJs\Block;
use Mep\WebToolkitBundle\Entity\EditorJs\EditorJsContent;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class EditorJsValidator extends ConstraintValidator
{
    /**
     * @param null|EditorJsContent|string $value
     * @param EditorJS                    $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (! $value instanceof EditorJsContent) {
            $this->context->buildViolation('Invalid data. EditorJs values must be arrays.')
                ->addViolation()
            ;
        }

        foreach ($value->getBlocks() as $arrayCollection) {
            $type = $arrayCollection::class;

            if (! in_array($type, $constraint->enabledTools, true)) {
                $this->context->buildViolation(
                    'Block type "'.Block::getTypeByClass($type).'" is not allowed in this content.',
                )
                    ->addViolation()
                ;
            }
        }
    }
}
