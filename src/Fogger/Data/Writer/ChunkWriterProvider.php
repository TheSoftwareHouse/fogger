<?php

namespace App\Fogger\Data\Writer;

use App\Fogger\Data\Writer\Exception\ChunkWriterNotFound;

class ChunkWriterProvider
{
    private $chunkWriters;

    public function __construct(iterable $writers)
    {
        foreach ($writers as $writer) {
            $this->addWriter($writer);
        }
    }

    public function addWriter(ChunkWriterInterface $chunkWriter)
    {
        $this->chunkWriters[] = $chunkWriter;
    }

    /**
     * @return ChunkWriterInterface
     * @throws ChunkWriterNotFound
     */
    public function getWriter(): ChunkWriterInterface
    {
        foreach ($this->chunkWriters as $chunkWriter) {
            if ($chunkWriter->isApplicable()) {
                return $chunkWriter;
            }
        }

        throw new ChunkWriterNotFound('Adapter that could write data could not be found');
    }
}
