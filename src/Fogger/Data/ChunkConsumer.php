<?php

namespace App\Fogger\Data;

class ChunkConsumer
{
    private $dataCopier;

    private $cache;

    private $error;

    public function __construct(
        DataCopier $dataCopier,
        ChunkCache $cache,
        ChunkError $error

    ) {
        $this->dataCopier = $dataCopier;
        $this->cache = $cache;
        $this->error = $error;
    }

    public function execute(ChunkMessage $message)
    {
        try {
            $this->dataCopier->copyDataChunk($message);
        } catch (\Exception $exception) {
            $this->error->addError($exception->getMessage());
        }

        $this->cache->increaseProcessedCount();
    }
}
