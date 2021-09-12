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

use Mep\WebToolkitBundle\Contract\FileStorage\ProcessorInterface;
use Mep\WebToolkitBundle\Dto\UnprocessedAttachmentDto;
use Mep\WebToolkitBundle\Exception\InvalidConfigurationException;
use RuntimeException;
use function Tinify\fromFile;
use Tinify\Tinify;

/**
 * This processor compresses images using the service by https://tinypng.com/.
 *
 * The "dummy mode" can be used when you want to support the compression feature, but you don't want to waste
 * money/bandwidth in specific environments (e.g. skip compression in "dev" environment).
 *
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
final class TinifyProcessor implements ProcessorInterface
{
    /**
     * @var string
     */
    public const IS_COMPRESSED = 'tinify_compressed';

    /**
     * @var string
     */
    public const IS_DUMMY = 'tinify_dummy';

    public function __construct(
        ?string $apiKey,
        private bool $dummyMode = false,
    ) {
        if (! $this->dummyMode) {
            if (null === $apiKey) {
                throw new InvalidConfigurationException('Missing Tinify API key.');
            }

            Tinify::setKey($apiKey);
        }
    }

    public function supports(UnprocessedAttachmentDto $unprocessedAttachmentDto): bool
    {
        if (
            ! isset($unprocessedAttachmentDto->processorsOptions['compress']) ||
                true !== $unprocessedAttachmentDto->processorsOptions['compress']
        ) {
            return false;
        }

        return in_array(mb_strtolower($unprocessedAttachmentDto->mimeType), ['image/jpeg', 'image/png'], true);
    }

    public function run(UnprocessedAttachmentDto $unprocessedAttachmentDto): UnprocessedAttachmentDto
    {
        $fileRealPath = $unprocessedAttachmentDto->file->getRealPath();

        if (! $fileRealPath) {
            throw new RuntimeException('Cannot compress image: invalid file path.');
        }

        if (! $this->dummyMode) {
            $tinifyFile = fromFile($fileRealPath);
            $tinifyFile->toFile($fileRealPath);

            clearstatcache(true, $fileRealPath);
            $unprocessedAttachmentDto->fileSize = $unprocessedAttachmentDto->file->getSize();
        } else {
            $unprocessedAttachmentDto->metadata[self::IS_DUMMY] = true;
        }

        $unprocessedAttachmentDto->metadata[self::IS_COMPRESSED] = true;
        unset($unprocessedAttachmentDto->processorsOptions['compress']);

        return $unprocessedAttachmentDto;
    }
}
