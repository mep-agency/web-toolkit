File Storage
============

Use local storage or S3 storage for file storing.

To make the system work, you should follow the next steps.

Controller
----------

Autowire ``FileStorageManager`` and use its methods to store, get public URL and remove the file::

    // src/Controller/StorageController.php

    // ...
    use Mep\WebToolkitBundle\Contract\FileStorage\FileStorageManager;

    class StorageController extends AbstractController
    {
        #[Route('/storage', name: 'storage')]
        public function storage(FileStorageManager $fileStorageManager): Response
        {
            // Stores a file with some optional metadata...
            $attachment = $fileStorageDriver->store(
                new File('/path/to/file.extension'),
                // A custom string that can be used to help garbage collection
                'my_custom_context',
                // Custom metadata
                ['metadata' => 'Metadata'],
                // Options for file processors
                [],
            );

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

Change ``type`` to ``attribute`` in ``doctrine.yaml``::

    // config/doctrine.yaml

    // ...

    App:
        is_bundle: false
        type: attribute
        dir: '%kernel.project_dir%/src/Entity'
        prefix: 'App\Entity'
        alias: App

Add the ``Mep\WebToolkitBundle\FileStorage\Driver\Local`` service in ``services.yaml`` to implement local storage in the ``dev`` environment::

    // config/services.yaml

    services:

        // ...

        # add more service definitions when explicit configuration is needed
        # please note that last definitions always *replace* previous ones
        mep_web_toolkit.file_storage_driver:
            class: Mep\WebToolkitBundle\FileStorage\Driver\Local
            arguments:
                $storagePath: '%kernel.project_dir%/public/storage'
                $publicUrlPathPrefix: '/storage'
                # optional
                #$publicUrlPrefix: 'http://127.0.0.1:8000'

    // ...

For ``prod`` environment add the ``Mep\WebToolkitBundle\FileStorage\Driver\S3`` service in ``services.yaml``::

    // config/prod/services.yaml

    services:

        // ...

        # add more service definitions when explicit configuration is needed
        # please note that last definitions always *replace* previous ones
        mep_web_toolkit.file_storage_driver:
            class: Mep\WebToolkitBundle\FileStorage\Driver\S3
            arguments:
                $region: 'region'
                $endpointUrl: 'endpointUrl'
                $key: 'key'
                $secret: 'secret'
                $bucketName: 'bucketName'
                $cdnUrl: 'cdnUrl'
                $objectsKeyPrefix: 'objectsKeyPrefix'

    // ...

Remember to replace the placeholders with actual data.