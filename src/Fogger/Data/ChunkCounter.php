<?php

namespace App\Fogger\Data;

use Predis\Client;

class ChunkCounter
{
    const CHUNKS_PUBLISHED = 'fogger::chunks_published';
    const CHUNKS_PROCESSED = 'fogger::chunks_processed';

    private $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function reset()
    {
        $this->redis->set(self::CHUNKS_PUBLISHED, 0);
        $this->redis->set(self::CHUNKS_PROCESSED, 0);
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
        return $this->redis->get(self::CHUNKS_PUBLISHED);
    }

    public function getProcessedCount(): int
    {
        return $this->redis->get(self::CHUNKS_PROCESSED);
    }
}
