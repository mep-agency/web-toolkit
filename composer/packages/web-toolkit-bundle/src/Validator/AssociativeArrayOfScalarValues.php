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

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AssociativeArrayOfScalarValues extends Constraint
{
    // Nothing to do here...
}
