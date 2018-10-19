<?php

namespace App\Fogger\Data;

use App\Fogger\Recipe\Table;
use App\Fogger\Subset\SubsetStrategyProvider;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class SourceQuery
{
    private $source;

    private $provider;

    public function __construct(Connection $source, SubsetStrategyProvider $provider)
    {
        $this->source = $source;
        $this->provider = $provider;
    }

    /**
     * @param Table $table
     * @return QueryBuilder
     * @throws \App\Fogger\Subset\Exception\UnknownSubsetStrategyException
     */
    public function getAllKeysQuery(Table $table)
    {
        $query = $this->getAllRowsQuery($table);
        $query
            ->resetQueryPart('select')
            ->select($this->source->quoteIdentifier($table->getSortBy()));

        return $query;
    }

    /**
     * @param Table $table
     * @param array $keys
     * @return QueryBuilder
     * @throws \App\Fogger\Subset\Exception\UnknownSubsetStrategyException
     */
    public function getAllRowsQuery(Table $table, array $keys = []): QueryBuilder
    {
        $query = $this->source->createQueryBuilder();
        $query
            ->select('*')
            ->from($this->source->quoteIdentifier($table->getName()));

        if (count($keys)) {
            $query
                ->where($query->expr()->in($table->getSortBy(), ':keys'))
                ->setParameter('keys', $keys, Connection::PARAM_STR_ARRAY);

            return $query;
        }
        $subset = $this->provider->getSubsetStrategy($table->getSubsetName());

        return $subset->subsetQuery($query, $table);
    }
}
