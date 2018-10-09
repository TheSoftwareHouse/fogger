<?php

namespace App\Fogger\Data;

use App\Fogger\Recipe\Recipe;
use App\Fogger\Recipe\Table;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ChunkProducer
{
    private $rabbitProducer;

    private $chunkDivider;

    private $serializer;

    private $chunkCounter;

    private $chunkError;

    public function __construct(
        ProducerInterface $rabbitProducer,
        ChunkDivider $chunkDivider,
        SerializerInterface $serializer,
        ChunkCounter $chunkCounter,
        ChunkError $chunkError
    )
    {
        $this->chunkDivider = $chunkDivider;
        $this->rabbitProducer = $rabbitProducer;
        $this->serializer = $serializer;
        $this->chunkCounter = $chunkCounter;
        $this->chunkError = $chunkError;
    }

    /**
     * @param Table $table
     * @param int $chunkSize
     * @throws \App\Fogger\Subset\Exception\UnknownSubsetStrategyException
     */
    private function queueTableChunks(Table $table)
    {
        $count = $this->chunkDivider->getNumberOfChunks($table);

        for ($i = 0; $i < $count; $i++) {
            $this->rabbitProducer->publish(
                $this->serializer->serialize(new ChunkMessage($table, $i), 'json')
            );

            $this->chunkCounter->increasePublishedCount();
        }
    }

    /**
     * @param Recipe $recipe
     * @throws \App\Fogger\Subset\Exception\UnknownSubsetStrategyException
     */
    public function run(Recipe $recipe)
    {
        $this->chunkCounter->reset();
        $this->chunkError->reset();
        foreach ($recipe->getTables() as $table) {
            $this->queueTableChunks($table);
        }
    }
}
