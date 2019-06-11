<?php

namespace App\Config\Model\Mongo;

class Config
{
    private $source;

    private $target;

    private $suffix;

    private $chunkSize;

    private $collections = [];

    public function __construct(string $source, string $target, int $chunkSize = 100, string $suffix = '')
    {
        $this->source = $source;
        $this->target = $target;
        $this->chunkSize = $chunkSize;
        $this->suffix = $suffix;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    public function getSuffix(): string
    {
        return $this->suffix;
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
