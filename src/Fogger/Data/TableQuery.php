<?php

namespace App\Fogger\Data;

use App\Fogger\Recipe\Table;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class TableQuery
{
    private $source;

    public function __construct(Connection $source)
    {
        $this->source = $source;
    }

    public function getAllRowsQuery(Table $table, $countOnly = false): QueryBuilder
    {
        $queryBuilder = $this->source->createQueryBuilder();

        $queryBuilder
            ->select($countOnly ? 'count(*)' : '*')
            ->from($this->source->quoteIdentifier($table->getName()));

        if (!$countOnly && $table->getSortBy() !== null) {
            $queryBuilder->OrderBy($table->getSortBy());
        }

        return $queryBuilder;
    }
}
