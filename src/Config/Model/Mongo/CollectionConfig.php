<?php

namespace App\Config\Model\Mongo;

class CollectionConfig
{
    private $paths = [];

    private $limit;

    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return PathConfig[]|array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    public function addPath(string $path, PathConfig $pathConfig): void
    {
        $this->paths[$path] = $pathConfig;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
