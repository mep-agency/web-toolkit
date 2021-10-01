EditorJS Field
==============

The EditorJS field is managed like a relation between entities.

Entity
------

Add the EditorJS property (with getter and setter) using the desired Doctrine relation and ``EditorJS`` attributes::

    // src/Entity/Article.php

    // ...
    use Mep\WebToolkitBundle\Entity\Attachment;
    use Mep\WebToolkitBundle\Validator\AttachmentFile;

    class Article
    {
        // ...

        #[ORM\OneToOne(targetEntity: EditorJsContent::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
        #[ORM\JoinColumn(nullable: false)]
        #[EditorJs(
            options: [
                Header::class => [
                    'levels' => [1, 2, 3, 4, 5],
                    'defaultLevel' => 2,
                ],
                Table::class => [
                    'rows' => 4,
                    'cols' => 3,
                ],
            ],
        )]
        private EditorJsContent $content;

        // ...
    }

It is possible to customize the EditorJS's properties by passing options to the ``EditorJs`` attribute.

CRUD Controller
---------------

Simply use the ``EditorJsField``::

    // src/Controller/Admin/ArticleCrudController.php

    // ...
    use Mep\WebToolkitBundle\Contract\Controller\Admin\AbstractCrudController;
    use Mep\WebToolkitBundle\Field\EditorJsField;

    class ArticleCrudController extends AbstractCrudController
    {
        // ...

        public function configureFields(string $pageName): iterable
        {
            return [
                IdField::new('id')->onlyOnIndex(),
                // ...
                EditorJsField::new('content')->onlyOnForms(),
            ];
        }
    }

Database queries
----------------
For searching in the content of an EditorJS use the property ``plainText``, like so::

    // src/Repository/ArticleRepository.php

    // ...

    class ArticleRepository extends ServiceEntityRepository
    {
        // ...

        public function search(?string $query): array
        {
            $queryBuilder = $this->createQueryBuilder('a')
                ->innerJoin(EditorJsContent::class, 'e', Join::WITH, 'p.content = e.id')
                ->andWhere(
                    $queryBuilder->expr()
                        ->like('e.plainText', ':query')
                )
                ->setParameter('query', '%'.$query.'%')
            ;

            return $queryBuilder->getQuery()
                ->getResult()
            ;
        }
    }

