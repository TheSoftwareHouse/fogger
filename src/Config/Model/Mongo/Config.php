<?php

namespace App\Config\Model\Mongo;

class Config
{
    private $source;

    private $target;

    private $collections = [];

    public function __construct(string $source, string $target)
    {
        $this->source = $source;
        $this->target = $target;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function addCollection(string $name, CollectionConfig $collectionConfig): void
    {
        $this->collections[$name] = $collectionConfig;
    }

    /**
     * @return CollectionConfig[]|array
     */
    public function getCollections(): array
    {
        return $this->collections;
    }
}
