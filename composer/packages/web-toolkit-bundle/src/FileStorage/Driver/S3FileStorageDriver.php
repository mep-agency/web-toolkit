<?php

declare(strict_types=1);

namespace Mep\WebToolkitBundle\FileStorage\Driver;

use Aws\S3\S3Client;
use JetBrains\PhpStorm\Pure;
use Mep\WebToolkitBundle\Contract\FileStorage\FileStorageDriverInterface;
use Mep\WebToolkitBundle\Entity\Attachment;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal Do not use this class directly, use the FileStorageManager class instead.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
final class S3FileStorageDriver implements FileStorageDriverInterface
{
    private S3Client $s3Client;

    public function __construct(
        private string $region,
        private string $endpointUrl,
        private string $key,
        private string $secret,
        private string $bucketName,
        private string $cdnUrl,
        private string $objectsKeyPrefix = '',
        private int $cdnCacheMaxAge = 604800,
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
            'CacheControl' => 'max-age=' . $this->cdnCacheMaxAge,
            'ACL' => 'public-read',
            'Key' => $this->buildFileKey($attachment),
            'SourceFile' => $file->getRealPath(),
            'ContentType' => $attachment->getMimeType(),
        ]);
    }

    #[Pure]
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

    #[Pure]
    public function getPublicUrl(Attachment $attachment): string
    {
        return $this->cdnUrl . '/' . $this->buildFileKey($attachment);
    }

    #[Pure]
    private function buildFileKey(Attachment $attachment): string
    {
        // Remove leading "/" from object keys
        return ltrim($this->objectsKeyPrefix . '/' . $attachment->getId() . '/' . $attachment->getFileName(), '/');
    }
}
