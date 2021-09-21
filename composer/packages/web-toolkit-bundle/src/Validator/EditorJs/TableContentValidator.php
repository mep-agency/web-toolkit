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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class TableContentValidator extends ConstraintValidator
{
    /**
     * @param null|mixed[]|string $content
     * @param TableContent        $constraint
     */
    public function validate($content, Constraint $constraint): void
    {
        if (null === $content || '' === $content) {
            return;
        }

        if (! is_array($content)) {
            $this->context->buildViolation('Invalid table content value.')
                ->addViolation()
            ;

            return;
        }

        foreach ($content as $rowKey => $row) {
            if (! is_int($rowKey)) {
                $this->context->buildViolation('Rows keys must be int values.')
                    ->addViolation()
                ;

                return;
            }

            if (! is_array($row)) {
                $this->context->buildViolation('Rows must be arrays.')
                    ->addViolation()
                ;

                return;
            }

            foreach ($row as $cellKey => $cellValue) {
                if (! is_int($cellKey)) {
                    $this->context->buildViolation('Cell keys must be int values.')
                        ->addViolation()
                    ;

                    return;
                }

                if (! is_string($cellValue)) {
                    $this->context->buildViolation('Row values must be strings.')
                        ->addViolation()
                    ;

                    return;
                }
            }
        }
    }
}
