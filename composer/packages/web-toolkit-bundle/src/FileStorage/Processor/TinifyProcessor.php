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

namespace Mep\WebToolkitBundle\FileStorage\Processor;

use Mep\WebToolkitBundle\Contract\FileStorage\FileStorageProcessorInterface;
use Mep\WebToolkitBundle\Dto\UnprocessedAttachmentDto;
use Mep\WebToolkitBundle\Exception\InvalidConfigurationException;
use function Tinify\fromFile as compressFromFile;
use Tinify\Tinify;

/**
 * This processor compresses images using the service by https://tinypng.com/.
 *
 * The "dummy mode" can be used when you want to support the compression feature, but you don't
 * want to waste money/bandwidth in specific environments (e.g. skip compression in "dev"
 * environment).
 *
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
final class TinifyProcessor implements FileStorageProcessorInterface
{
    public const IS_COMPRESSED = 'tinify_compressed';
    public const IS_DUMMY = 'tinify_dummy';

    public function __construct(
        ?string      $apiKey,
        private bool $dummyMode = false,
    ) {
        if (! $this->dummyMode) {
            if ($apiKey === null) {
                throw new InvalidConfigurationException('Missing Tinify API key.');
            }

            Tinify::setKey($apiKey);
        }
    }

    public function supports(UnprocessedAttachmentDto $attachment): bool
    {
        if (
            ! isset($attachment->processorsOptions['compress']) ||
                $attachment->processorsOptions['compress'] !== true
        ) {
            return false;
        }

        return in_array(mb_strtolower($attachment->mimeType), ['image/jpeg', 'image/png'], true);
    }

    public function run(UnprocessedAttachmentDto $attachment): UnprocessedAttachmentDto
    {
        if (! $this->dummyMode) {
            $tinifyFile = compressFromFile($attachment->file->getRealPath());
            $tinifyFile->toFile($attachment->file->getRealPath());

            clearstatcache(true, $attachment->file->getRealPath());
            $attachment->fileSize = $attachment->file->getSize();
        } else {
            $attachment->metadata[self::IS_DUMMY] = true;
        }

        $attachment->metadata[self::IS_COMPRESSED] = true;
        unset($attachment->processorsOptions['compress']);

        return $attachment;
    }
}
