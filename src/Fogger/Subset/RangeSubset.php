<?php

namespace App\Fogger\Subset;

use App\Fogger\Recipe\Table;
use Doctrine\DBAL\Query\QueryBuilder;

class RangeSubset extends AbstractSubset
{
    /**
     * @param array $options
     * @throws Exception\RequiredOptionMissingException
     */
    private function ensureValidOptions(array $options)
    {
        $this->ensureOptionIsSet($options, 'column');
        $this->ensureOptionIsSet($options, 'from');
        $this->ensureOptionIsSet($options, 'to');
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Table $table
     * @throws Exception\RequiredOptionMissingException
     */
    public function subsetQuery(QueryBuilder $queryBuilder, Table $table)
    {
        $this->ensureValidOptions($options = $table->getSubset()->getOptions());

        $queryBuilder
            ->where(sprintf('%s >= ?', $options['column']))
            ->andWhere(sprintf('%s <= ?', $options['column']))
            ->setParameter(0, $options['from'])
            ->setParameter(1, $options['to']);
    }

    protected function getSubsetStrategyName(): string
    {
        return 'range';
    }
}
