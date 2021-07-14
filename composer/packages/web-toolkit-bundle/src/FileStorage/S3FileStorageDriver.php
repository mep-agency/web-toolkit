<?php

declare(strict_types=1);

namespace Mep\WebToolkitBundle\FileStorage;

use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;
use Mep\WebToolkitBundle\Contract\FileStorage\FileStorageDriverInterface;
use Mep\WebToolkitBundle\Entity\Attachment;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class S3FileStorageDriver implements FileStorageDriverInterface
{
    private S3Client $s3Client;

    public function __construct(
        private string $region,
        private string $endpointUrl,
        private string $key,
        private string $secret,
        private string $bucketName,
        private string $cdnUrl,
        private EntityManagerInterface $entityManager,
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

    public function store(File $file, array $metadata = []): Attachment
    {
        if (! $filePath = $file->getRealPath()) {
            throw new FileNotFoundException(
                null,
                0,
                null,
                $file->getPathname()
            );
        }

        $attachment = new Attachment(
            $file->getFilename(),
            $file->getMimeType() ?? 'application/octet-stream',
            $file->getSize(),
            $metadata,
        );

        // Copy new file to storage
        $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'CacheControl' => 'max-age=' . $this->cdnCacheMaxAge,
            'ACL' => 'public-read',
            'Key' => $this->buildFileKey($attachment),
            'SourceFile' => $filePath,
            'ContentType' => $attachment->getMimeType(),
        ]);

        $this->entityManager->persist($attachment);
        $this->entityManager->flush();

        return $attachment;
    }

    public function remove(Attachment $attachment): void
    {
        $this->entityManager->remove($attachment);
        $this->entityManager->flush();

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
