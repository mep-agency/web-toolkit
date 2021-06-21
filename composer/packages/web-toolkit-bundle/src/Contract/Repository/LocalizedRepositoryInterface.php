<?php


namespace Mep\WebToolkitBundle\Contract\Repository;


use Doctrine\ORM\QueryBuilder;

interface LocalizedRepositoryInterface
{
    public function createLocalizedQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder;

    public function localizeQueryBuilder(QueryBuilder $queryBuilder): QueryBuilder;
}