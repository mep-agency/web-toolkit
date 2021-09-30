Attachment Field
================

Attachments are managed like relations between entities.

Entity
------

Add the attachment property (with getter and setter) using the desired Doctrine relation and ``AttachmentFile`` attributes::

    // src/Entity/Article.php

    // ...
    use Mep\WebToolkitBundle\Entity\Attachment;
    use Mep\WebToolkitBundle\Validator\AttachmentFile;

    class Article
    {
        // ...

        #[ORM\OneToOne(targetEntity: Attachment::class, cascade: ['persist', 'remove'])]
        #[AttachmentFile(
            maxSize: 10000, // In bytes
            allowedMimeTypes: ['#application/pdf#'],
            allowedNamePattern: '#[.+].+#',
            metadata: ['metadata1' => 'First Metadata'],
            processorsOptions: ['compress' => false],
        )]
        private ?Attachment $attachment = null;

        // ...
    }

It is possible to customize the attachment's properties by passing options to the ``AttachmentFile`` attribute.

CRUD Controller
---------------

Simply use the ``AttachmentField``::

    // src/Controller/Admin/ArticleCrudController.php

    // ...
    use Mep\WebToolkitBundle\Contract\Controller\Admin\AbstractCrudController;
    use Mep\WebToolkitBundle\Field\AttachmentField;

    class ArticleCrudController extends AbstractCrudController
    {
        // ...

        public function configureFields(string $pageName): iterable
        {
            return [
                IdField::new('id')->onlyOnIndex(),
                // ...
                AttachmentField::new('attachment')->onlyOnForms(),
            ];
        }
    }

