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
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController as OriginalAbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
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
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Mep\WebToolkitBundle\Contract\Repository\AbstractSingleInstanceRepository;
use Mep\WebToolkitBundle\Contract\Repository\LocalizedRepositoryInterface;
use Mep\WebToolkitBundle\Dto\AdminAttachmentUploadDto;
use Mep\WebToolkitBundle\FileStorage\FileStorageManager;
use Mep\WebToolkitBundle\Form\AdminAttachmentUploadApiType;
use RuntimeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
abstract class AbstractCrudController extends OriginalAbstractCrudController
{
    public const ACTION_DELETE_TRANSLATION = 'deleteTranslation';

    public const ACTION_ATTACH_FILE = 'attachFile';

    public function __construct(
        protected LocaleProviderInterface $localeProvider,
        protected AdminContextProvider $adminContextProvider,
        protected AdminUrlGenerator $adminUrlGenerator,
        protected FileStorageManager $fileStorageManager,
        protected NormalizerInterface $normalizer,
    ) {}

    public function createEntity(string $entityFqcn)
    {
        $instance = parent::createEntity($entityFqcn);

        $this->overrideDefaultLocaleIfIsTranslatable($instance);

        return $instance;
    }

    public function index(AdminContext $context)
    {
        return $this->redirectToSingleInstance($context) ?? parent::index($context);
    }

    public function new(AdminContext $context)
    {
        return $this->redirectToSingleInstance($context) ?? parent::new($context);
    }

    /**
     * Redirects to:
     * - the NEW action if no instance is found
     * - the EDIT action if an instance is found
     */
    private function redirectToSingleInstance(AdminContext $context): ?Response
    {
        if ($this->isSingleInstance()) {
            $repository = $this->getRepository();

            $singleInstance = $repository->getInstance();

            if ($singleInstance === null) {
                if ($context->getCrud()->getCurrentAction() === Action::NEW) {
                    // This is already a NEW action, no redirect needed
                    return null;
                }

                return $this->redirect(
                    $this->get(AdminUrlGenerator::class)
                        ->setAction(Action::NEW)
                        ->generateUrl()
                );
            }

            /** @var EntityFactory $entityFactory */
            $entityFactory = $this->get(EntityFactory::class);

            return $this->redirect(
                $this->get(AdminUrlGenerator::class)
                    ->setAction(Action::EDIT)
                    ->setEntityId($entityFactory->createForEntityInstance($singleInstance)->getPrimaryKeyValue())
                    ->generateUrl()
            );
        }

        return null;
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
                            fn(object $instance) => !($instance instanceof TranslatableInterface) || $instance->getTranslations()->count() > 1
                        )
                );
        }

        return $actionsConfiguration;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        static::mergeNewTranslationsIfIsTranslatable($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        static::mergeNewTranslationsIfIsTranslatable($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteTranslation(AdminContext $context)
    {
        $event = new BeforeCrudActionEvent($context);
        $this->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => self::ACTION_DELETE_TRANSLATION, 'entity' => $context->getEntity()])) {
            throw new ForbiddenActionException($context);
        }

        if (!$context->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($context);
        }

        $instance = $context->getEntity()->getInstance();

        if ($instance instanceof TranslatableInterface) {
            $currentLocale = $this->localeProvider->provideCurrentLocale();
            $translations = $instance->getTranslations();
            $currentTranslation = $translations->get($currentLocale);

            if ($translations->count() <= 1) {
                throw new RuntimeException(
                    'Cannot delete a translation if it\'s the only one available.'
                );
            }

            if ($currentTranslation !== null) {
                $instance->removeTranslation($currentTranslation);

                $this->updateEntity(
                    $this->get('doctrine')
                        ->getManagerForClass(
                            $context->getEntity()
                                ->getFqcn()
                        ),
                    $instance
                );

                $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
                    'entity' => $context->getEntity(),
                ]));

                $event = new AfterCrudActionEvent($context, $responseParameters);
                $this->get('event_dispatcher')->dispatch($event);
                if ($event->isPropagationStopped()) {
                    return $event->getResponse();
                }

                return $this->redirect($this->adminUrlGenerator->setAction(Action::INDEX)->unset(EA::ENTITY_ID)->generateUrl());
            }

            throw new RuntimeException(
                'Trying to delete "' . $currentLocale . '" translation but only the following were available: ' . implode(', ', $instance->getTranslations()->getKeys())
            );
        }

        throw new RuntimeException('Trying to perform "deleteTranslation" action on a non-translatable entity.');
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        /** @var TranslatableInterface $instance */
        $instance = $entityDto->getInstance();

        $this->overrideDefaultLocaleIfIsTranslatable($instance);

        return parent::createEditFormBuilder($entityDto, $formOptions, $context);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if (self::isTranslatableEntity()) {
            $entityRepository = $this->getDoctrine()->getRepository($entityDto->getFqcn());

            if (! $entityRepository instanceof LocalizedRepositoryInterface) {
                throw new RuntimeException('Repositories of Translatable entities must implement the LocalizedRepositoryInterface');
            }

            $entityRepository->localizeQueryBuilder($queryBuilder);
        }

        return $queryBuilder;
    }

    private function getRepository(): ObjectRepository
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->get('doctrine');

        return $doctrine->getRepository(static::getEntityFqcn());
    }

    private function isSingleInstance(): bool
    {
        return $this->getRepository() instanceof AbstractSingleInstanceRepository;
    }

    private function overrideDefaultLocaleIfIsTranslatable(?object $instance): void
    {
        if ($instance !== null && $instance instanceof TranslatableInterface) {
            $instance->setDefaultLocale(
                $this->localeProvider
                    ->provideCurrentLocale()
            );

            // Ensure a new translation is ready for the current locale (if needed)
            $instance->translate(null, false);
        }
    }

    private static function isTranslatableEntity(): bool
    {
        return in_array(
            TranslatableInterface::class,
            class_implements(static::getEntityFqcn()),
            true);
    }

    private static function mergeNewTranslationsIfIsTranslatable(?object $instance): void
    {
        if ($instance instanceof TranslatableInterface) {
            $instance->mergeNewTranslations();
        }
    }

    public function attachFile(AdminContext $context): JsonResponse
    {
        if ($context->getRequest()->getMethod() !== Request::METHOD_POST) {
            throw new BadRequestException('A request to "' . self::ACTION_ATTACH_FILE . '" must use "' . Request::METHOD_POST . '" HTTP method.');
        }

        $form = $this->createForm(
            AdminAttachmentUploadApiType::class,
            null,
            // Using EA::ROUTE_PARAMS to have them included in URL signature validation
            $context->getRequest()->get(EA::ROUTE_PARAMS)
        );
        $form->handleRequest($context->getRequest());

        if (! $form->isSubmitted()) {
            throw new BadRequestException('Expected form data cannot be found.');
        }

        if (! $form->isValid()) {
            $errors = [];

            foreach ($form->getErrors(true) as $error) {
                $errors[] = [
                    'property' => (string) $error->getOrigin()->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
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
        $propertyPath = $form->getConfig()->getOption(AdminAttachmentUploadApiType::PROPERTY_PATH);
        /** @var array<string, scalar> $metadata */
        $metadata = $form->getConfig()->getOption(AdminAttachmentUploadApiType::METADATA);
        /** @var array<string, scalar> $metadata */
        $processorsOptions = $form->getConfig()->getOption(AdminAttachmentUploadApiType::PROCESSORS_OPTIONS);
        $frontEndContext = $formData->context !== null ? '#' . $formData->context : '';

        if (! isset($metadata['context'])) {
            $metadata['context'] = static::getEntityFqcn() . '@' . $propertyPath . $frontEndContext;
        }

        $attachment = $this->fileStorageManager->store($formData->file, $metadata, $processorsOptions);

        return new JsonResponse($this->normalizer->normalize($attachment, 'json'));
    }
}