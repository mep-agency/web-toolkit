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

namespace Mep\WebToolkitBundle\Contract\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T as object
 * @template-extends ServiceEntityRepository<T>
 *
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
abstract class AbstractSingleInstanceRepository extends ServiceEntityRepository
{
    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(ManagerRegistry $managerRegistry, string $entityClass)
    {
        parent::__construct($managerRegistry, $entityClass);
    }

    public function getInstance(): ?object
    {
        return $this->findOneBy([]);
    }
}
