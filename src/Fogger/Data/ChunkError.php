<?php

namespace App\Fogger\Data;

use Predis\Client;

class ChunkError
{
    const CHUNKS_ERROR = 'fogger::chunks_error';

    private $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function reset(): void
    {
        $this->redis->set(self::CHUNKS_ERROR, '');
    }

    public function addError(string $errorMessage): void
    {
        $this->redis->append(self::CHUNKS_ERROR, trim($errorMessage) . "\n");
    }

    public function getError(): string
    {
        return (string)trim($this->redis->get(self::CHUNKS_ERROR));
    }

    public function hasError(): bool
    {
        return $this->redis->get(self::CHUNKS_ERROR) !== '';
    }
}
