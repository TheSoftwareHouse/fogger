<?php

namespace App\Config\Model;

class ColumnConfig
{
    const NONE_STRATEGY = 'none';

    private $maskStrategy;

    private $options;

    public function __construct(string $maskStrategy = self::NONE_STRATEGY, array $options = [])
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
