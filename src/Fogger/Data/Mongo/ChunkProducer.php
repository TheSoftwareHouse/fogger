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
    private $client;

    private $source;

    private $target;

    private $suffix;

    private $chunkCache;

    private $chunkError;

    public function __construct(Client $client, ChunkCache $chunkCache, ChunkError $chunkError)
    {
        $this->client = $client;
        $this->chunkCache = $chunkCache;
        $this->chunkError = $chunkError;
    }

    private function queueCollectionChunks(string $collectionName, CollectionConfig $collectionConfig, int $chunkSize)
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
        $options = ['projection' => ['_id' => 1]];
        if ($collectionConfig->getLimit() > 0) {
            $options['limit'] = $collectionConfig->getLimit();
        }
        $cursor = $collection->find([], $options);

        $counter = 0;
        $keys = [];

        /** @var BSONDocument $document */
        foreach ($cursor as $document) {
            $arrayCopy = $document->getArrayCopy();
            $document->getArrayCopy();
            $keys[] = (string)$arrayCopy['_id'];
            $counter++;
            if (0 === $counter % $chunkSize) {
                $this->chunkCache->pushMessage($this->source, $this->target, $this->suffix, $recipe, $keys);
                $keys = [];
            }
        }

        if (0 !== $counter % $chunkSize) {
            $this->chunkCache->pushMessage($this->source, $this->target, $this->suffix, $recipe, $keys);
        }
    }

    public function run(Config $config)
    {
        $this->chunkCache->reset();
        $this->chunkError->reset();
        $this->source = $config->getSource();
        $this->target = $config->getTarget();
        $this->suffix = $config->getSuffix();
        /** @var CollectionConfig $collection */
        foreach ($config->getCollections() as $name => $collection) {
            $this->queueCollectionChunks($name, $collection, $config->getChunkSize());
        }
    }
}
