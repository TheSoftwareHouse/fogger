<?php

namespace App\Fogger\Data;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Serializer\SerializerInterface;

class ChunkConsumer implements ConsumerInterface
{
    private $dataCopier;

    private $serializer;

    private $counter;

    private $error;

    public function __construct(
        DataCopier $dataCopier,
        SerializerInterface $serializer,
        ChunkCounter $counter,
        ChunkError $error

    ) {
        $this->dataCopier = $dataCopier;
        $this->serializer = $serializer;
        $this->counter = $counter;
        $this->error = $error;
    }

    public function execute(AMQPMessage $msg)
    {
        try {
            /** @var ChunkMessage $chunkMessage */
            $chunkMessage = $this->serializer->deserialize($msg->getBody(), ChunkMessage::class, 'json');
            $this->dataCopier->copyDataChunk($chunkMessage);
        } catch (\Exception $exception) {
            $this->error->addError($exception->getMessage());
        }

        $this->counter->increaseProcessedCount();
    }
}
