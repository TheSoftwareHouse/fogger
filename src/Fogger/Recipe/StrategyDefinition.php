<?php

namespace App\Fogger\Recipe;

class StrategyDefinition
{
    private $name;

    private $options;

    /**
     * StrategyDefinition constructor.
     * @param $name
     * @param $options
     */
    public function __construct(string $name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
