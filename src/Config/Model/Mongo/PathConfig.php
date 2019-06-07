<?php

namespace App\Config\Model\Mongo;

class PathConfig
{
    private $maskStrategy;

    private $options;

    public function __construct(string $maskStrategy, array $options = [])
    {
        $this->maskStrategy = $maskStrategy;
        $this->options = $options;
    }

    public function getMaskStrategy(): string
    {
        return $this->maskStrategy;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
