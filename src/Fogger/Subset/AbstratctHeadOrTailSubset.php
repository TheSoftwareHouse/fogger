<?php

namespace App\Fogger\Subset;

use App\Fogger\Data\TableQuery;
use App\Fogger\Recipe\Table;
use App\Fogger\Subset\Exception\SortByColumnRequired;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstratctHeadOrTailSubset extends AbstractSubset
{
    private $tableQuery;

    public function __construct(TableQuery $query)
    {
        $this->tableQuery = $query;
    }

    /**
     * @param Table $table
     * @throws SortByColumnRequired
     */
    protected function ensureValidPrimaryKey(Table $table)
    {
        if (null === $table->getSortBy()) {
            throw new SortByColumnRequired(
                sprintf(
                    'Error! Strategy require the table to have a unique sortBy column',
                    $table->getName()
                )
            );
        }
    }

    private function reverseOrderBy(QueryBuilder $queryBuilder, Table $table)
    {
        $queryBuilder->resetQueryPart('orderBy');
        $queryBuilder->addOrderBy($table->getSortBy(), 'DESC');
    }

    protected function findOffsetId(Table $table, bool $reverse)
    {
        $options = $table->getSubset()->getOptions();

        $findOffsetId = $this->tableQuery->getAllRowsQuery($table);
        if ($reverse) {
            $this->reverseOrderBy($findOffsetId, $table);
        }
        $findOffsetId
            ->setFirstResult($options['length'] - 1)
            ->setMaxResults(1);

        $idRow = $findOffsetId->execute()->fetch();

        return $idRow[$table->getSortBy()];
    }
}
