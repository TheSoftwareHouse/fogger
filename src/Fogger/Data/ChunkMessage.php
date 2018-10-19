<?php

namespace App\Fogger\Data;

use App\Fogger\Recipe\Table;

class ChunkMessage
{
    const DEFAULT_CHUNK_SIZE = 2000;

    private $table;

    private $keys;

    public function __construct(Table $table, array $keys)
    {
        $this->table = $table;
        $this->keys = $keys;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getKeys(): array
    {
        return $this->keys;
    }
}
