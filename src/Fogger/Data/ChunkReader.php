<?php

namespace App\Fogger\Data;

use App\Fogger\Subset\SubsetStrategyProvider;

class ChunkReader
{
    private $query;

    private $subsetStrategyProvider;

    public function __construct(TableQuery $query, SubsetStrategyProvider $subsetStrategyProvider)
    {
        $this->query = $query;
        $this->subsetStrategyProvider = $subsetStrategyProvider;
    }

    /**
     * @param ChunkMessage $chunkMessage
     * @return array
     * @throws \App\Fogger\Subset\Exception\UnknownSubsetStrategyException
     */
    public function getDataChunk(ChunkMessage $chunkMessage): array
    {
        $query = $this->query->getAllRowsQuery($chunkMessage->getTable());
        $subsetStrategy = $this->subsetStrategyProvider->getSubsetStrategy($chunkMessage->getStrategyName());
        $subsetStrategy->subsetQuery($query, $chunkMessage->getTable());
        $query
            ->setMaxResults($chunkMessage->getLimit())
            ->setFirstResult($chunkMessage->getOffset());

        return $query->execute()->fetchAll();
    }
}
