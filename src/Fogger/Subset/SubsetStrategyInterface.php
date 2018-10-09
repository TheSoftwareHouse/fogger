<?php

namespace App\Fogger\Subset;

use App\Fogger\Recipe\Table;
use Doctrine\DBAL\Query\QueryBuilder;

interface SubsetStrategyInterface
{
    public function subsetQuery(QueryBuilder $queryBuilder, Table $table);

    public function supports(string $name): bool;
}
