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

namespace Mep\WebToolkitBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class EditorJsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint EditorJs */

        if (null === $value || '' === $value) {
            return;
        }

        if (! is_array($value)) {
            $this->context->buildViolation('Invalid data. EditorJs values must be arrays.')
                ->addViolation();
        }

        foreach ($value['blocks'] as $block) {
            if (! in_array($type = $block['type'], $constraint->enabledTools, true)) {
                $this->context->buildViolation('Invalid block. Block type "' . $type . '" is not allowed in this content.')
                    ->addViolation();
            }
        }
    }
}
