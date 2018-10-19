<?php

namespace App\Fogger\Subset;

use App\Fogger\Recipe\Table;
use Doctrine\DBAL\Query\QueryBuilder;

class TailSubset extends AbstratctHeadOrTailSubset
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param Table $table
     * @return QueryBuilder
     * @throws Exception\RequiredOptionMissingException
     * @throws Exception\SortByColumnRequired
     */
    public function subsetQuery(QueryBuilder $queryBuilder, Table $table): QueryBuilder
    {
        $this->ensureOptionIsSet($table->getSubset()->getOptions(), 'length');
        $this->ensureSortByColumn($table);

        return $queryBuilder
            ->andWhere(sprintf('%s >= ?', $table->getSortBy()))
            ->setParameter(0, $this->findOffsetId($table, true));
    }

    public function getSubsetStrategyName(): string
    {
        return 'tail';
    }
}
