<?php

namespace App\Config\Model;

class TableConfig
{
    private $columns = [];

    private $subsetStrategy = null;

    private $subsetOptions = [];

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getColumn(string $name): ?ColumnConfig
    {
        return $this->columns[$name] ?? null;
    }

    public function addColumn(string $name, ColumnConfig $column)
    {
        $this->columns[$name] = $column;
    }

    public function getSubsetStrategy(): ?string
    {
        return $this->subsetStrategy;
    }

    public function getSubsetOptions(): array
    {
        return $this->subsetOptions;
    }

    public function setSubsetStrategy(string $name, array $options)
    {
        $this->subsetStrategy = $name;
        $this->subsetOptions = $options;
    }
}
