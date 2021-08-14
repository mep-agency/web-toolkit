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

namespace Mep\WebToolkitBundle\Exception\Entity;

use RuntimeException;
use Mep\WebToolkitBundle\Contract\Repository\AbstractSingleInstanceRepository;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class MultipleInstancesOfSingleInstanceEntityException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Cannot persist multiple instances of an entity managed by a ' . AbstractSingleInstanceRepository::class);
    }
}
