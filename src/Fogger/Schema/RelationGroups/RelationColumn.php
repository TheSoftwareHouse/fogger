<?php

namespace App\Fogger\Schema\RelationGroups;

class RelationColumn
{
    const DESCRIPTOR_PATTERN = '%s.%s';

    private $table;

    private $columns;

    public function __construct(string $table, array $columns)
    {
        $this->table = $table;
        $this->columns = $columns;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getDescriptor()
    {
        return sprintf(
            self::DESCRIPTOR_PATTERN,
            $this->table,
            implode('|', $this->columns)
        );
    }
}
