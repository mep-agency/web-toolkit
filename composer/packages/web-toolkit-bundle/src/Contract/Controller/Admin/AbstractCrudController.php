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

namespace Mep\WebToolkitBundle\Contract\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController as OriginalAbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Mep\WebToolkitBundle\Contract\Repository\AbstractSingleInstanceRepository;
use Mep\WebToolkitBundle\Contract\Repository\LocalizedRepositoryInterface;
use Mep\WebToolkitBundle\Dto\AdminAttachmentUploadDto;
use Mep\WebToolkitBundle\FileStorage\FileStorageManager;
use Mep\WebToolkitBundle\Form\AdminAttachmentType;
use Mep\WebToolkitBundle\Form\AdminAttachmentUploadApiType;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @template T of object
 *
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
abstract class AbstractCrudController extends OriginalAbstractCrudController
{
    /**
     * @var string
     */
    final public const ACTION_DELETE_TRANSLATION = 'deleteTranslation';

    /**
     * @var string
     */
    final public const ACTION_ATTACH_FILE = 'attachFile';

    public function __construct(
        protected LocaleProviderInterface $localeProvider,
        protected AdminContextProvider $adminContextProvider,
        protected AdminUrlGenerator $adminUrlGenerator,
        protected FileStorageManager $fileStorageManager,
        protected NormalizerInterface $normalizer,
        protected EventDispatcherInterface $eventDispatcher,
        protected EntityManagerInterface $entityManager,
        protected EntityFactory $entityFactory,
    ) {
    }

    /**
     * @psalm-return T
     */
    public function createEntity(string $entityFqcn)
    {
        $instance = parent::createEntity($entityFqcn);

        $this->overrideDefaultLocaleIfIsTranslatable($instance);

        return $instance;
    }

    public function index(AdminContext $adminContext): KeyValueStore|Response
    {
        return $this->redirectToSingleInstance($adminContext) ?? parent::index($adminContext);
    }

    public function new(AdminContext $adminContext): KeyValueStore|Response
    {
        return $this->redirectToSingleInstance($adminContext) ?? parent::new($adminContext);
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);

        if ($this->isSingleInstance()) {
            // Reset page title to entity label (singular)
            $crud->setPageTitle(Action::NEW, '%entity_label_singular%');
            $crud->setPageTitle(Action::EDIT, '%entity_label_singular%');
        }

        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actionsConfiguration = parent::configureActions($actions);

        if ($this->isSingleInstance()) {
            // Remove useless actions
            $actionsConfiguration->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
            $actionsConfiguration->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
        }

        if (self::isTranslatableEntity()) {
            $actionsConfiguration
                ->add(
                    Crud::PAGE_EDIT,
                    Action::new(self::ACTION_DELETE_TRANSLATION, null, 'fas fa-language')
                        ->linkToCrudAction('deleteTranslation')
                        ->addCssClass('btn')
                        ->displayIf(
                            fn (object $instance) => ! ($instance instanceof TranslatableInterface) || $instance->getTranslations()
                                ->count() > 1,
                        ),
                )
            ;
        }

        return $actionsConfiguration;
    }

    public function configureAssets(Assets $assets): Assets
    {
        $assets
            // Add assets for AttachmentFields
            ->addCssFile(Asset::new('bundles/webtoolkit/attachment-field.css')->onlyOnForms())
            ->addJsFile(Asset::new('bundles/webtoolkit/attachment-field.js')->onlyOnForms())
            // Add assets for EditorJsFields
            ->addCssFile(Asset::new('bundles/webtoolkit/editorjs-field.css')->onlyOnForms())
            ->addJsFile(Asset::new('bundles/webtoolkit/editorjs-field.js')->onlyOnForms())
        ;

        return $assets;
    }

    /**
     * @param T $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        static::mergeNewTranslationsIfIsTranslatable($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @param T $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        static::mergeNewTranslationsIfIsTranslatable($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteTranslation(AdminContext $adminContext): Response|RedirectResponse
    {
        $event = new BeforeCrudActionEvent($adminContext);
        $this->eventDispatcher->dispatch($event);

        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (! $this->isGranted(Permission::EA_EXECUTE_ACTION, [
            'action' => self::ACTION_DELETE_TRANSLATION,
            'entity' => $adminContext->getEntity(),
        ])) {
            throw new ForbiddenActionException($adminContext);
        }

        if (! $adminContext->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($adminContext);
        }

        /** @var T $instance */
        $instance = $adminContext->getEntity()
            ->getInstance()
        ;

        if ($instance instanceof TranslatableInterface) {
            $currentLocale = $this->localeProvider->provideCurrentLocale();

            if (null === $currentLocale) {
                throw new RuntimeException('Cannot get current locale.');
            }

            $translations = $instance->getTranslations();
            /** @var null|TranslationInterface $currentTranslation */
            $currentTranslation = $translations->get($currentLocale);

            if ($translations->count() <= 1) {
                throw new RuntimeException("Cannot delete a translation if it's the only one available.");
            }

            if (null !== $currentTranslation) {
                $instance->removeTranslation($currentTranslation);

                $this->updateEntity($this->entityManager, $instance);

                $keyValueStore = $this->configureResponseParameters(KeyValueStore::new([
                    'entity' => $adminContext->getEntity(),
                ]));

                $event = new AfterCrudActionEvent($adminContext, $keyValueStore);
                $this->eventDispatcher->dispatch($event)
                ;
                if ($event->isPropagationStopped()) {
                    return $event->getResponse();
                }

                return $this->redirect(
                    $this->adminUrlGenerator->setAction(Action::INDEX)->unset(EA::ENTITY_ID)->generateUrl(),
                );
            }

            throw new RuntimeException(
                'Trying to delete "'.$currentLocale.'" translation but only the following were available: '.implode(
                ', ',
                $instance->getTranslations()->getKeys(),
            ),
            );
        }

        throw new RuntimeException('Trying to perform "deleteTranslation" action on a non-translatable entity.');
    }

    public function attachFile(AdminContext $adminContext): JsonResponse
    {
        if (Request::METHOD_POST !== $adminContext->getRequest()->getMethod()) {
            throw new BadRequestException(
                'A request to "'.self::ACTION_ATTACH_FILE.'" must use "'.Request::METHOD_POST.'" HTTP method.',
            );
        }

        // Using EA::ROUTE_PARAMS to have them included in URL signature validation
        /** @var array<string, mixed> $options */
        $options = $adminContext->getRequest()->get(EA::ROUTE_PARAMS);

        $form = $this->createForm(AdminAttachmentUploadApiType::class, null, $options);
        $form->handleRequest($adminContext->getRequest());

        if (! $form->isSubmitted()) {
            throw new BadRequestException('Expected form data cannot be found.');
        }

        if (! $form->isValid()) {
            $errors = [];

            foreach ($form->getErrors(true) as $error) {
                if ($error instanceof FormError) {
                    $errors[] = [
                        'property' => (string) $error->getOrigin()
                            ?->getPropertyPath(),
                        'message' => $error->getMessage(),
                    ];
                }
            }

            return new JsonResponse(
                [
                    'message' => 'Invalid form data',
                    'errors' => $errors,
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }

        /** @var AdminAttachmentUploadDto $formData */
        $formData = $form->getData();
        /** @var ?string $context */
        $context = $form->getConfig()->getOption(AdminAttachmentType::CONTEXT);
        /** @var array<string, scalar> $metadata */
        $metadata = $form->getConfig()
            ->getOption(AdminAttachmentType::METADATA)
        ;
        /** @var array<string, scalar> $processorsOptions */
        $processorsOptions = $form->getConfig()
            ->getOption(AdminAttachmentType::PROCESSORS_OPTIONS)
        ;

        $attachment = $this->fileStorageManager
            ->store($formData->file, $context, $metadata, $processorsOptions)
        ;

        return new JsonResponse($this->normalizer->normalize($attachment, 'json'));
    }

    public function createEditFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $keyValueStore,
        AdminContext $adminContext,
    ): FormBuilderInterface {
        /** @var T $instance */
        $instance = $entityDto->getInstance();

        $this->overrideDefaultLocaleIfIsTranslatable($instance);

        return parent::createEditFormBuilder($entityDto, $keyValueStore, $adminContext);
    }

    /**
     * @param FieldCollection<FieldDto>   $fieldCollection
     * @param FilterCollection<FilterDto> $filterCollection
     */
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fieldCollection,
        FilterCollection $filterCollection,
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fieldCollection, $filterCollection);

        if (self::isTranslatableEntity()) {
            /** @var class-string<TranslatableInterface> $entityFqcn */
            $entityFqcn = $entityDto->getFqcn();

            $entityRepository = $this->entityManager->getRepository($entityFqcn);

            if (! $entityRepository instanceof LocalizedRepositoryInterface) {
                throw new RuntimeException(
                    'Repositories of Translatable entities must implement the LocalizedRepositoryInterface',
                );
            }

            $entityRepository->localizeQueryBuilder($queryBuilder);
        }

        return $queryBuilder;
    }

    /**
     * @return class-string<T>
     */
    abstract public static function getEntityFqcn(): string;

    /**
     * @param null|T $instance
     */
    protected static function mergeNewTranslationsIfIsTranslatable(?object $instance): void
    {
        if ($instance instanceof TranslatableInterface) {
            $instance->mergeNewTranslations();
        }
    }

    /**
     * @param null|T $instance
     */
    private function overrideDefaultLocaleIfIsTranslatable(?object $instance): void
    {
        if ($instance instanceof TranslatableInterface) {
            $currentLocale = $this->localeProvider->provideCurrentLocale();

            if (null === $currentLocale) {
                throw new RuntimeException('Cannot get current locale.');
            }

            $instance->setDefaultLocale($currentLocale);

            // Ensure a new translation is ready for the current locale (if needed)
            $instance->translate(null, false);
        }
    }

    /**
     * Redirects to:
     * - the NEW action if no instance is found
     * - the EDIT action if an instance is found.
     */
    private function redirectToSingleInstance(AdminContext $adminContext): ?Response
    {
        if ($this->isSingleInstance()) {
            /** @var AbstractSingleInstanceRepository<T> $repository */
            $repository = $this->getRepository();

            $singleInstance = $repository->getInstance();

            if (null === $singleInstance) {
                if (Action::NEW === $adminContext->getCrud()?->getCurrentAction()) {
                    // This is already a NEW action, no redirect needed
                    return null;
                }

                return $this->redirect($this->adminUrlGenerator->setAction(Action::NEW)->generateUrl());
            }

            return $this->redirect(
                $this->adminUrlGenerator->setAction(Action::EDIT)
                    ->setEntityId($this->entityFactory->createForEntityInstance($singleInstance)->getPrimaryKeyValue())
                    ->generateUrl(),
            );
        }

        return null;
    }

    private function isSingleInstance(): bool
    {
        return $this->getRepository() instanceof AbstractSingleInstanceRepository;
    }

    /**
     * @return EntityRepository<T>
     */
    private function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(static::getEntityFqcn());
    }

    private static function isTranslatableEntity(): bool
    {
        return is_a(static::getEntityFqcn(), TranslatableInterface::class, true);
    }
}
