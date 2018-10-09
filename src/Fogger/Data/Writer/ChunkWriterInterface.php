<?php

namespace App\Fogger\Data\Writer;

interface ChunkWriterInterface
{
    public function insert(string $table, array $data);

    public function isApplicable(): bool;
}
