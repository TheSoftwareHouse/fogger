<?php

namespace App\Fogger\Data;

class ChunkReader
{
    private $sourceQuery;

    public function __construct(SourceQuery $sourceQuery)
    {
        $this->sourceQuery = $sourceQuery;
    }

    /**
     * @param ChunkMessage $chunkMessage
     * @return array
     * @throws \App\Fogger\Subset\Exception\UnknownSubsetStrategyException
     */
    public function getDataChunk(ChunkMessage $chunkMessage): array
    {
        $query = $this->sourceQuery->getAllRowsQuery(
            $chunkMessage->getTable(),
            $chunkMessage->getKeys()
        );

        return $query->execute()->fetchAll();
    }
}
