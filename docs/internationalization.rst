Internationalization
====================

Internationalization is managed using `DoctrineBehaviors Translatable`_ entities.

To make the system work, you should follow the next steps.

Base entity
-----------

Use ``TranslatableTrait``, example::

    // src/Entity/Article.php

    // ...
    use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
    use Mep\WebToolkitBundle\Contract\Entity\TranslatableTrait;

    /**
     * @ORM\Entity(repositoryClass=ArticleRepository::class)
     */
    class Article implements TranslatableInterface
    {
        use TranslatableTrait;

        // ...
    }

The trait simply forces property validation.

Base repository
---------------

Implement ``LocalizedRepositoryInterface`` and use ``LocalizedRepositoryTrait``, like this::

    // src/Repository/ArticleRepository.php

    // ...
    use Mep\WebToolkitBundle\Contract\Repository\LocalizedRepositoryInterface;
    use Mep\WebToolkitBundle\Contract\Repository\LocalizedRepositoryTrait;

    // ...
    class ArticleRepository extends ServiceEntityRepository implements LocalizedRepositoryInterface
    {
    use LocalizedRepositoryTrait;

        // ...
    }

This trait is needed to create a localized QueryBuilder.

CRUD controller
---------------

extend MEP ``AbstractCrudController``::

    // src/Controller/Admin/ArticleCrudController.php

    // ...
    use Mep\WebToolkitBundle\Contract\Controller\Admin\AbstractCrudController;

    class ArticleCrudController extends AbstractCrudController
    {
        // ...
    }

This abstract controller adds these features:

- Ensures localized instances to exist when needed
- Adds a "Delete translation" button, if there is more than one translation
- Merges new translations before persist or update an entity
- Joins translatable properties for index view

Configuration
-------------

Add ``app.locales`` and ``app.default_locale`` in ``service.yaml``, like so::

    // config/service.yaml

    // ...

    parameters:
        app.locales: ['en', 'it']
        app.default_locale: 'en'

    // ...


Replace ``default_locale`` and ``fallbacks`` in ``translations.yaml``::

    // config/packages/translations.yaml

    framework:
        default_locale: '%app.default_locale%'
        translator:
            default_path: '%kernel.project_dir%/translations'
            fallbacks:
                - '%app.default_locale%'

    // ...

Add global ``locales`` in ``twig.yaml``::

    // config/packages/twig.yaml

    twig:
        default_path: '%kernel.project_dir%/templates'

        globals:
            locales: '%app.locales%'

    // ...

And finally add ``prefix`` in ``annotations.yaml``::

    // config/routes/annotations.yaml

    controllers:
        resource: ../../src/Controller/
        type: annotation
        prefix:
            en: '/en'
            it: '/it'

    // ...

.. _`DoctrineBehaviors Translatable`: https://github.com/KnpLabs/DoctrineBehaviors/blob/master/docs/translatable.md