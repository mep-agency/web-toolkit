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

namespace Mep\WebToolkitBundle\FileStorage\Driver;

use Aws\S3\S3Client;
use Mep\WebToolkitBundle\Contract\FileStorage\DriverInterface;
use Mep\WebToolkitBundle\Entity\Attachment;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal do not use this class directly, use the FileStorageManager class instead
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
final class S3Driver implements DriverInterface
{
    private readonly S3Client $s3Client;

    public function __construct(
        private readonly string $region,
        private readonly string $endpointUrl,
        private readonly string $key,
        private readonly string $secret,
        private readonly string $bucketName,
        private readonly string $cdnUrl,
        private readonly string $objectsKeyPrefix = '',
        private readonly int $cdnCacheMaxAge = 604800,
    ) {
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => $this->region,
            'endpoint' => $this->endpointUrl,
            'credentials' => [
                'key' => $this->key,
                'secret' => $this->secret,
            ],
            'http' => [
                'connect_timeout' => 5,
                'timeout' => 10,
            ],
        ]);
    }

    public function store(File $file, Attachment $attachment): void
    {
        // Copy new file to storage
        $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'CacheControl' => 'max-age='.$this->cdnCacheMaxAge,
            'ACL' => 'public-read',
            'Key' => $this->buildFileKey($attachment),
            'SourceFile' => $file->getRealPath(),
            'ContentType' => $attachment->getMimeType(),
        ]);
    }

    public function attachedFileExists(Attachment $attachment): bool
    {
        return ! $this->s3Client->doesObjectExist($this->bucketName, $this->buildFileKey($attachment));
    }

    public function removeAttachedFile(Attachment $attachment): void
    {
        $this->s3Client->deleteObject([
            'Bucket' => $this->bucketName,
            'Key' => $this->buildFileKey($attachment),
        ]);
    }

    public function getPublicUrl(Attachment $attachment): string
    {
        return $this->cdnUrl.'/'.$this->buildFileKey($attachment);
    }

    private function buildFileKey(Attachment $attachment): string
    {
        // Remove leading "/" from object keys
        return ltrim($this->objectsKeyPrefix.'/'.$attachment->getId().'/'.$attachment->getFileName(), '/');
    }
}
