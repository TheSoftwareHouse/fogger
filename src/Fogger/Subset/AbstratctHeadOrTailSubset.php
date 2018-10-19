<?php

namespace App\Fogger\Subset;

use App\Fogger\Recipe\Table;
use App\Fogger\Subset\Exception\SortByColumnRequired;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;

abstract class AbstratctHeadOrTailSubset extends AbstractSubset
{
    private $source;

    public function __construct(Connection $source)
    {
        $this->source = $source;
    }

    /**
     * @param Table $table
     * @throws SortByColumnRequired
     */
    protected function ensureSortByColumn(Table $table)
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

    protected function findOffsetId(Table $table, bool $reverse)
    {
        $options = $table->getSubset()->getOptions();

        $findOffsetId = $this->source->createQueryBuilder();
        $findOffsetId
            ->select($this->source->quoteIdentifier($table->getSortBy()))
            ->from($this->source->quoteIdentifier($table->getName()))
            ->addOrderBy($table->getSortBy(), $reverse ? Criteria::DESC : Criteria::ASC)
            ->setFirstResult($options['length'] - 1)
            ->setMaxResults(1);

        return $findOffsetId->execute()->fetchColumn();
    }
}
