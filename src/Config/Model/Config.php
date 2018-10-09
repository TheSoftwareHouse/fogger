<?php

namespace App\Config\Model;

class Config
{
    private $tables = [];

    private $excludes = [];

    /**
     * @return TableConfig[]
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    public function addTable(string $name, TableConfig $table)
    {
        $this->tables[$name] = $table;
    }

    public function getTable($name): ?TableConfig
    {
        return $this->tables[$name] ?? null;
    }

    public function setExcludes(array $excludes)
    {
        $this->excludes = $excludes;
    }

    public function getExcludes(): array
    {
        return $this->excludes;
    }
}
