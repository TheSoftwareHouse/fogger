<?php

namespace App\Fogger\Schema\RelationGroups;

class GrouppedRelationColumns
{
    private $columns = [];

    public function addRelationColumn(RelationColumn $column)
    {
        if ($this->contains($column)) {
            return;
        }

        $this->columns[$column->getDescriptor()] = $column;
    }

    public function contains(RelationColumn $column)
    {
        return $this->containsByKey($column->getDescriptor());
    }

    public function containsByKey(string $key)
    {
        return isset($this->columns[$key]);
    }

    /**
     * @return RelationColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }
}
