<?php

namespace App\Fogger\Data\Mongo;

use App\Config\Model\Mongo\CollectionConfig;
use App\Config\Model\Mongo\Config;
use App\Fogger\Data\ChunkError;
use App\Fogger\Recipe\Mongo\Collection;
use App\Fogger\Recipe\StrategyDefinition;
use MongoDB\Client;
use MongoDB\Model\BSONDocument;

class ChunkProducer
{
    const CHUNK_SIZE = 1000;

    private $client;

    private $source;

    private $target;

    private $chunkCache;

    private $chunkError;

    public function __construct(Client $client, ChunkCache $chunkCache, ChunkError $chunkError)
    {
        $this->client = $client;
        $this->chunkCache = $chunkCache;
        $this->chunkError = $chunkError;
    }

    private function queueCollectionChunks(string $collectionName, CollectionConfig $collectionConfig)
    {
        $recipe = new Collection($collectionName);
        foreach ($collectionConfig->getPaths() as $path => $definition) {
            $recipe->addMask(
                $path,
                new StrategyDefinition($definition->getMaskStrategy(), $definition->getOptions())
            );
        }

        $source = $this->source;

        $collection = $this->client->$source->$collectionName;
        $cursor = $collection->find([], ['projection' => ['_id' => 1]]);

        $counter = 0;
        $keys = [];

        /** @var BSONDocument $document */
        foreach ($cursor as $document) {
            $arrayCopy = $document->getArrayCopy();
            $document->getArrayCopy();
            $keys[] = (string)$arrayCopy['_id'];
            $counter++;
            if (0 === $counter % self::CHUNK_SIZE) {
                $this->chunkCache->pushMessage($this->source, $this->target, $recipe, $keys);
                $keys = [];
            }
        }

        if (0 !== $counter % self::CHUNK_SIZE) {
            $this->chunkCache->pushMessage($this->source, $this->target, $recipe, $keys);
        }
    }

    public function run(Config $config)
    {
        $this->chunkCache->reset();
        $this->chunkError->reset();
        $this->source = $config->getSource();
        $this->target = $config->getTarget();
        /** @var CollectionConfig $collection */
        foreach ($config->getCollections() as $name => $collection) {
            $this->queueCollectionChunks($name, $collection);
        }
    }
}
