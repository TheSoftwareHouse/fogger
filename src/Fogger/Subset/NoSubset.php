<?php

namespace App\Fogger\Subset;

use App\Fogger\Recipe\Table;
use Doctrine\DBAL\Query\QueryBuilder;

class NoSubset extends AbstractSubset
{
    const STRATEGY_NAME = 'noSubset';

    public function subsetQuery(QueryBuilder $queryBuilder, Table $table): QueryBuilder
    {
        return $queryBuilder;
    }

    public function getSubsetStrategyName(): string
    {
        return self::STRATEGY_NAME;
    }
}
