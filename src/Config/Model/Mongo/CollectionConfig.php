<?php

namespace App\Config\Model\Mongo;

class CollectionConfig
{
    private $paths = [];

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
}
