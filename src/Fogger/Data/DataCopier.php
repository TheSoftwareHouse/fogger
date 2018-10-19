<?php

namespace App\Fogger\Data;

use App\Fogger\Data\Writer\ChunkWriterProvider;

class DataCopier
{
    private $chunkReader;

    private $masker;

    private $chunkWriterProvider;

    public function __construct(
        ChunkReader $chunkReader,
        Masker $masker,
        ChunkWriterProvider $chunkWriterchunkWriterProvider
    ) {
        $this->chunkReader = $chunkReader;
        $this->masker = $masker;
        $this->chunkWriterProvider = $chunkWriterchunkWriterProvider;
    }

    /**
     * @param ChunkMessage $chunkMessage
     * @throws \App\Fogger\Mask\Exception\UnknownMaskException
     * @throws \App\Fogger\Subset\Exception\UnknownSubsetStrategyException
     * @throws Writer\Exception\ChunkWriterNotFound
     */
    public function copyDataChunk(ChunkMessage $chunkMessage)
    {
        $data = $this->chunkReader->getDataChunk($chunkMessage);
        $table = $chunkMessage->getTable();
        $this->chunkWriterProvider->getWriter()->insert(
            $table->getName(),
            $this->masker->applyMasks($data, $table->getMasks())
        );
    }
}
