<?php

namespace App\Fogger\Recipe\Mongo;

use App\Fogger\Recipe\StrategyDefinition;

class Collection
{
    private $name;

    private $masks = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return StrategyDefinition[]
     */
    public function getMasks(): array
    {
        return $this->masks;
    }

    public function addMask(string $path, StrategyDefinition $mask)
    {
        $this->masks[$path] = $mask;
    }
}
