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

use LogicException;
use Mep\WebToolkitBundle\Entity\EditorJs\EditorJsContent;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * @internal do not use this class directly
 *
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
final class EditorJsContentNormalizer implements DenormalizerInterface
{
    public function __construct(
        private ObjectNormalizer $objectNormalizer,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $data
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): object
    {
        /** @var int $time */
        $time = $data['time'];
        $data['time'] = (string) $time;
        /** @var array<string, mixed>[] $blocks */
        $blocks = $data['blocks'];

        foreach ($blocks as &$block) {
            if (! is_iterable($block['data'])) {
                throw new LogicException('Data is not of the correct type.');
            }

            foreach ($block['data'] as $key => $value) {
                $block[$key] = $value;
            }

            unset($block['data']);
        }

        $denormalizedData = $this->objectNormalizer->denormalize($data, $type, $format, $context);

        if (! is_object($denormalizedData)) {
            throw new LogicException('Data is not of the correct type.');
        }

        return $denormalizedData;
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return is_array($data) && EditorJsContent::class === $type && 'json' === $format;
    }
}
