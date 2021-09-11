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

namespace Mep\WebToolkitBundle\FileStorage\GarbageCollector;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Generator;
use Mep\WebToolkitBundle\Contract\Attachment\AttachmentsGarbageCollectorInterface;
use Mep\WebToolkitBundle\Dto\AttachmentContextDto;
use Mep\WebToolkitBundle\Entity\Attachment;

/**
 * Uses Doctrine metadata to find all associations to Attachment objects and collects unused
 * Attachments thanks to the "context" metadata.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
final class ContextGarbageCollector implements AttachmentsGarbageCollectorInterface
{
    public function collect(EntityManagerInterface $entityManager, bool $dryRun): Generator
    {
        $entities = $entityManager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();

        foreach($entities as $entity) {
            foreach ($entityManager->getClassMetadata($entity)->getAssociationMappings() as $mapping) {
                if ($mapping['targetEntity'] === Attachment::class) {
                    $attachmentRepository = $entityManager->getRepository(Attachment::class);
                    $queryBuilder = $attachmentRepository->createQueryBuilder('a')
                        ->leftJoin($entity, 'p', Join::WITH, 'p.attachment = a.id')
                        ->andWhere('p.' . $mapping['fieldName'] . ' IS NULL AND JSON_EXTRACT(a.metadata, \'$.context\') = :context')
                        ->setParameter('context', (string) (new AttachmentContextDto($entity, $mapping['fieldName'])))
                    ;

                    yield from $queryBuilder->getQuery()->getResult();
                }
            }
        }
    }
}
