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

namespace Mep\WebToolkitBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Exception\FileStorage\AttachmentNotFoundException;
use Mep\WebToolkitBundle\FileStorage\FileStorageManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentExtension extends AbstractExtension
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileStorageManager $fileStorageManager,
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [new TwigFunction('attachment_public_url', function (Attachment|string $attachment): string {
            return $this->getPublicUrl($attachment);
        })];
    }

    public function getPublicUrl(Attachment|string $attachment): string
    {
        if ($attachment instanceof Attachment) {
            $uuid = $attachment->getId();
        } else {
            $uuid = $attachment;
            $attachment = $this->entityManager
                ->getRepository(Attachment::class)
                ->find($uuid)
            ;
        }

        if (! $attachment instanceof Attachment) {
            throw new AttachmentNotFoundException($uuid);
        }

        return $this->fileStorageManager->getPublicUrl($attachment);
    }
}
