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

namespace Mep\WebToolkitBundle\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Mep\WebToolkitBundle\Contract\Repository\AbstractSingleInstanceRepository;
use Mep\WebToolkitBundle\Exception\Entity\MultipleInstancesOfSingleInstanceEntityException;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class ForceSingleInstanceEventListener
{
    public function prePersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $object = $lifecycleEventArgs->getObject();

        $repository = $lifecycleEventArgs->getObjectManager()
            ->getRepository($object::class)
        ;

        if ($repository instanceof AbstractSingleInstanceRepository && null !== $repository->getInstance()) {
            throw new MultipleInstancesOfSingleInstanceEntityException();
        }
    }
}
