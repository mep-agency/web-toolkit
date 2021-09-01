<?php

declare(strict_types=1);

namespace Mep\WebToolkitBundle\Twig;

use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Exception\FileStorage\AttachmentNotFoundException;
use Mep\WebToolkitBundle\FileStorage\FileStorageManager;
use Mep\WebToolkitBundle\Repository\AttachmentRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentExtension extends AbstractExtension
{
    public function __construct(
        private AttachmentRepository $attachmentRepository,
        private FileStorageManager $fileStorageManager,
    ) {}

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [new TwigFunction('attachment_public_url', [$this, 'getPublicUrl'])];
    }

    /**
     * @param Attachment|string $attachment
     */
    public function getPublicUrl($attachment): string
    {
        if (!($attachment instanceof Attachment)) {
            $uuid = $attachment;
            $attachment = $this->attachmentRepository->find($uuid);
        }

        if ($attachment === null) {
            throw new AttachmentNotFoundException($uuid);
        }

        return $this->fileStorageManager->getPublicUrl($attachment);
    }
}
