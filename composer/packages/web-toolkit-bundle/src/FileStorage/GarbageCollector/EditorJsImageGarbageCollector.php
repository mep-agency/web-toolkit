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
use Mep\WebToolkitBundle\Contract\Attachment\AttachmentsGarbageCollectorInterface;
use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Image;

/**
 * Collects all attachments that were associated with image blocks and are now orphans.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
final class EditorJsImageGarbageCollector implements AttachmentsGarbageCollectorInterface
{
    /**
     * @return Attachment[]
     */
    public function collect(EntityManagerInterface $entityManager, bool $dryRun): array
    {
        $attachmentRepository = $entityManager->getRepository(Attachment::class);
        $queryBuilder = $attachmentRepository->createQueryBuilder('a')
            ->leftJoin(Image::class, 'i', Join::WITH, 'i.attachment = a.id')
            ->andWhere('i.attachment IS NULL AND JSON_EXTRACT(a.metadata, \'$.context\') LIKE :context')
            ->setParameter('context', '%#' . Image::ATTACHMENTS_CONTEXT)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
