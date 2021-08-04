File Storage
============

Use local storage or S3 storage for file storing.

To make the system work, you should follow the next steps.

Controller
----------

Autowire ``FileStorageDriverInterface`` and use its methods to store, get public URL and remove the file::

    // src/Controller/StorageController.php

    // ...
    use Mep\WebToolkitBundle\Contract\FileStorage\FileStorageDriverInterface;

    class StorageController extends AbstractController
    {
        #[Route('/storage', name: 'storage')]
        public function storage(FileStorageDriverInterface $fileStorageDriver): Response
        {
            // Stores file with some optional metadata...
            $attachment = $fileStorageDriver->store(new File('/path/to/file.extension'), ['metadata' => 'Metadata']);

            // Gets public URL...
            $attachmentPublicUrl = $fileStorageDriver->getPublicUrl($attachment)

            // Removes file...
            $fileStorageDriver->remove($attachment);

            // ...
        }

        // ...
    }

Configuration
-------------

Add ``LocalFileStorageDriver`` service in ``services.yaml`` to implement local storage in the ``dev`` environment::

    // config/services.yaml

    services:

        // ...

        # add more service definitions when explicit configuration is needed
        # please note that last definitions always *replace* previous ones
        Mep\WebToolkitBundle\Contract\FileStorage\FileStorageDriverInterface: '@Mep\WebToolkitBundle\FileStorage\LocalFileStorageDriver'
        Mep\WebToolkitBundle\FileStorage\LocalFileStorageDriver:
            arguments:
                $storagePath: '%kernel.project_dir%/public/storage'
                $publicUrlPathPrefix: '/storage'
                # optional
                #$publicUrlPrefix: 'http://127.0.0.1:8000'

    // ...

For ``prod`` environment instead add ``S3FileStorageDriver`` service in ``services.yaml``::

    // config/prod/services.yaml

    services:

        // ...

        # add more service definitions when explicit configuration is needed
        # please note that last definitions always *replace* previous ones
        Mep\WebToolkitBundle\Contract\FileStorage\FileStorageDriverInterface: '@Mep\WebToolkitBundle\FileStorage\S3FileStorageDriver'
        Mep\WebToolkitBundle\FileStorage\S3FileStorageDriver:
            arguments:
                $region: 'region'
                $endpointUrl: 'endpointUrl'
                $key: 'key'
                $secret: 'secret'
                $bucketName: 'bucketName'
                $cdnUrl: 'cdnUrl'
                $objectsKeyPrefix: 'objectsKeyPrefix'

    // ...

