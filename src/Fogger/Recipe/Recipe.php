<?php

namespace App\Fogger\Recipe;

class Recipe
{
    private $tables = [];

    private $excludes;

    public function __construct(array $excludes)
    {
        $this->excludes = $excludes;
    }

    public function getTables(): array
    {
        return $this->tables;
    }

    public function getExcludes(): array
    {
        return $this->excludes;
    }

    public function addTable(string $name, Table $table)
    {
        $this->tables[$name] = $table;
    }

    public function getTable(string $name)
    {
        return $this->tables[$name] ?? null;
    }
}
