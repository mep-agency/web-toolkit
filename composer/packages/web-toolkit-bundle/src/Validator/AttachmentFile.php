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
 * @final You should not extend this class.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class AttachmentFile extends Constraint
{
    /**
     * Please note that processors options can't be validated, but they are used for type-guessing.
     *
     * @param string[] $allowedMimeTypes
     * @param array<string, scalar> $metadata
     * @param array<string, scalar> $processorsOptions
     */
    public function __construct(
        public int $maxSize = 0,
        public array $allowedMimeTypes = [],
        public ?string $allowedNamePattern = null,
        public array $metadata = [],
        public array $processorsOptions = [],
    ) {
        parent::__construct();
    }
}
