<?php

namespace App\Fogger\Data;

use App\Fogger\Recipe\Table;
use App\Fogger\Subset\SubsetStrategyProvider;

class ChunkDivider
{
    private $tableQuery;

    private $subsetStrategyProvider;

    /**
     * ChunkDivider constructor.
     * @param TableQuery $query
     * @param SubsetStrategyProvider $subsetStrategyProvider
     */
    public function __construct(TableQuery $query, SubsetStrategyProvider $subsetStrategyProvider)
    {
        $this->tableQuery = $query;
        $this->subsetStrategyProvider = $subsetStrategyProvider;
    }

    /**
     * @param Table $table
     * @return int
     * @throws \App\Fogger\Subset\Exception\UnknownSubsetStrategyException
     */
    protected function getSubsetRowsCount(Table $table): int
    {
        $query = $this->tableQuery->getAllRowsQuery($table, true);
        $subsetStrategy = $this->subsetStrategyProvider->getSubsetStrategy($table->getSubsetName());
        $subsetStrategy->subsetQuery($query, $table);

        return (int)$query->execute()->fetchColumn();
    }

    /**
     * @param Table $table
     * @return int
     * @throws \App\Fogger\Subset\Exception\UnknownSubsetStrategyException
     */
    public function getNumberOfChunks(Table $table): int
    {
        $rowsCount = $this->getSubsetRowsCount($table);
        if ($table->getSortBy() === null) {
            $table->setChunkSize($rowsCount);

            return 1;
        }

        return ceil($rowsCount / $table->getChunkSize());
    }
}
