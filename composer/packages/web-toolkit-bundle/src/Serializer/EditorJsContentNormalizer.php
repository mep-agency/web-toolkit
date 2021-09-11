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

use Mep\WebToolkitBundle\Entity\EditorJs\EditorJsContent;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * @internal do not use this class directly
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
final class EditorJsContentNormalizer implements DenormalizerInterface
{
    public function __construct(
        private ObjectNormalizer $objectNormalizer,
    ) {
    }

    public function denormalize($data, string $type, string $format = null, array $context = []): object
    {
        $data['time'] = (string) $data['time'];

        foreach ($data['blocks'] as &$block) {
            foreach ($block['data'] as $key => $value) {
                $block[$key] = $value;
            }

            unset($block['data']);
        }

        return $this->objectNormalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return is_array($data) && EditorJsContent::class === $type && 'json' === $format;
    }
}
