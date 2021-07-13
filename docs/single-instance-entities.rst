Single instance entities
========================

Single instance entities are used for creating standalone back office pages that are not linked to any entity (like settings pages).

Here's the steps for the implementation:

Base repository
---------------

Extend ``AbstractSingleInstanceRepository`` instead of ``ServiceEntityRepository``, like this::

    // src/Repository/HomepageSettingsRepository.php

    // ...
    use Mep\WebToolkitBundle\Contract\Repository\AbstractSingleInstanceRepository;

    // ...
    class HomepageSettingsRepository extends AbstractSingleInstanceRepository
    {
        public function __construct(ManagerRegistry $registry)
        {
            parent::__construct($registry, HomepageSettings::class);
        }

        // ...
    }

Simply adds ``getInstance()`` method.

CRUD controller
---------------

extend MEP ``AbstractCrudController``::

    // src/Controller/Admin/HomepageSettingsCrudController.php

    // ...
    use Mep\WebToolkitBundle\Contract\Controller\Admin\AbstractCrudController;

    class HomepageSettingsCrudController extends AbstractCrudController
    {
        // ...
    }

This abstract controller adds these features:

- Redirects to ``New`` or ``Edit`` page, skipping ``Index``
- Removes useless buttons like ``Save and add another`` and ``Save and continue``