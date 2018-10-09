<?php

namespace App\Fogger\Data;

use App\Fogger\Recipe\Table;

class ChunkMessage
{
    private $table;

    private $chunkNumber;

    public function __construct(Table $table, int $chunkNumber)
    {
        $this->table = $table;
        $this->chunkNumber = $chunkNumber;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getLimit(): int
    {
        return $this->table->getChunkSize();
    }

    public function getOffset(): int
    {
        return $this->getTable()->getChunkSize() * $this->chunkNumber;
    }

    public function getStrategyName(): string
    {
        return $this->table->getSubset()->getName();
    }

    public function getMasks(): array
    {
        return $this->table->getMasks();
    }

    public function getChunkNumber(): int
    {
        return $this->chunkNumber;
    }
}
