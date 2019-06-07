<?php

namespace App\Fogger\Data\Mongo;

use App\Fogger\Recipe\Mongo\Collection;
use Predis\Client;
use Symfony\Component\Serializer\SerializerInterface;

class ChunkCache
{
    const LIST_NAME = 'mogger::chunks';
    const CHUNKS_PUBLISHED = 'mogger::chunks_published';
    const CHUNKS_PROCESSED = 'mogger::chunks_processed';

    private $redis;

    private $serializer;

    public function __construct(Client $redis, SerializerInterface $serializer)
    {
        $this->redis = $redis;
        $this->serializer = $serializer;
    }

    public function reset()
    {
        $this->redis->del(
            [
                self::CHUNKS_PUBLISHED,
                self::CHUNKS_PROCESSED,
                self::LIST_NAME,
            ]
        );
    }

    public function pushMessage(string $source, string $target, Collection $collection, array $keys = [])
    {
        $message = $this->serializer->serialize(new ChunkMessage($source, $target, $collection, $keys), 'json');
        $this->redis->rpush(self::LIST_NAME, [$message]);
        $this->increasePublishedCount();
    }

    public function popMessage()
    {
        if (null === $content = $this->redis->lpop(self::LIST_NAME)) {
            return null;
        }

        return $this->serializer->deserialize($content, ChunkMessage::class, 'json');
    }

    public function increasePublishedCount()
    {
        $this->redis->incr(self::CHUNKS_PUBLISHED);
    }

    public function increaseProcessedCount()
    {
        $this->redis->incr(self::CHUNKS_PROCESSED);
    }

    public function getPublishedCount(): int
    {
        return $this->redis->get(self::CHUNKS_PUBLISHED) ?? 0;
    }

    public function getProcessedCount(): int
    {
        return $this->redis->get(self::CHUNKS_PROCESSED) ?? 0;
    }
}
