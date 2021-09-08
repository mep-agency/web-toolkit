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

namespace Mep\WebToolkitBundle\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\FileStorage\FileStorageManager;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal Do not use this class directly.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private FileStorageManager $fileStorageManager,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @param Attachment $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return [
            'uuid' => $object->getId()->toRfc4122(),
            'publicUrl' => $this->fileStorageManager->getPublicUrl($object),
        ];
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Attachment && $format === 'json';
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $this->entityManager->getRepository(Attachment::class)
            ->find($data['uuid']);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return is_array($data) && $type === Attachment::class && $format === 'json';
    }
}
