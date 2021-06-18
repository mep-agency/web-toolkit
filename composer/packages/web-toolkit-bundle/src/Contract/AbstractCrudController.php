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

namespace Mep\WebToolkitBundle\Contract;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use RuntimeException;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
abstract class AbstractCrudController extends OriginalAbstractCrudController
{
    public const ACTION_DELETE_TRANSLATION = 'deleteTranslation';

    public function __construct(
        protected LocaleProviderInterface $localeProvider,
        protected AdminUrlGenerator $adminUrlGenerator,
    ) {}

    public function createEntity(string $entityFqcn)
    {
        $instance = parent::createEntity($entityFqcn);

        $this->overrideDefaultLocaleIfIsTranslatable($instance);

        return $instance;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actionsConfiguration = parent::configureActions($actions);

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

        if (self::isTranslatableEntity($entityDto)) {
            /** @var class-string<TranslatableInterface> $entityFqcn */
            $entityFqcn = $entityDto->getFqcn();
            $rootAlias = $queryBuilder->getRootAliases()[0];

            $queryBuilder
                ->innerJoin(
                    $entityFqcn::getTranslationEntityClass(),
                    'translation',
                    Join::WITH,
                    $rootAlias . '.' . $entityDto->getPrimaryKeyName() . ' = translation.translatable AND translation.locale = :locale'
                )
                ->setParameter('locale', $this->localeProvider->provideCurrentLocale())
            ;
        }

        return $queryBuilder;
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

    private static function isTranslatableEntity(?EntityDto $entityDto = null): bool
    {
        if ($entityDto !== null) {
            $entityFqcn = $entityDto->getFqcn();
        } else {
            $entityFqcn = static::getEntityFqcn();
        }

        return in_array(
            TranslatableInterface::class,
            class_implements($entityFqcn),
            true);
    }

    private static function mergeNewTranslationsIfIsTranslatable(?object $instance): void
    {
        if ($instance instanceof TranslatableInterface) {
            $instance->mergeNewTranslations();
        }
    }
}