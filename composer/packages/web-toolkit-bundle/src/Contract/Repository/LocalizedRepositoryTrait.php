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

namespace Mep\WebToolkitBundle\Contract\Repository;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
trait LocalizedRepositoryTrait
{
    private LocaleProviderInterface $localeProvider;

    /**
     * @return class-string<TranslatableInterface>
     */
    abstract public function getClassName();

    /**
     * Creates a new QueryBuilder instance that is prepopulated for this entity name.
     *
     * @param string $indexBy the index for the from
     *
     * @return QueryBuilder
     */
    abstract public function createQueryBuilder(string $alias, $indexBy = null);

    public function getLocaleProvider(): LocaleProviderInterface
    {
        return $this->localeProvider;
    }

    #[Required]
    public function setLocaleProvider(LocaleProviderInterface $localeProvider): void
    {
        $this->localeProvider = $localeProvider;
    }

    public function createLocalizedQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return $this->localizeQueryBuilder($this->createQueryBuilder($alias, $indexBy));
    }

    public function localizeQueryBuilder(QueryBuilder $queryBuilder): QueryBuilder
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        return $queryBuilder
            ->innerJoin(
                $this->getClassName()::getTranslationEntityClass(),
                'translation',
                Join::WITH,
                $rootAlias.'.'.$this->getClassMetadata()->getSingleIdentifierFieldName().' = translation.translatable AND translation.locale = :locale',
            )
            ->setParameter('locale', $this->getLocaleProvider()->provideCurrentLocale())
        ;
    }

    /**
     * @return ClassMetadata
     */
    abstract protected function getClassMetadata();
}
