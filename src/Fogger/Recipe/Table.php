<?php

namespace App\Fogger\Recipe;

class Table
{
    private $name;

    private $sortBy;

    private $subset;

    private $masks = [];

    private $chunkSize;

    public function __construct(string $name, int $chunkSize, ?string $sortBy, StrategyDefinition $subset)
    {
        $this->name = $name;
        $this->sortBy = $sortBy;
        $this->subset = $subset;
        $this->chunkSize = $chunkSize;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function getSubset(): StrategyDefinition
    {
        return $this->subset;
    }

    /**
     * @return StrategyDefinition[]
     */
    public function getMasks(): array
    {
        return $this->masks;
    }

    public function addMask(string $column, StrategyDefinition $mask)
    {
        $this->masks[$column] = $mask;
    }

    public function getSubsetName(): string
    {
        return $this->getSubset()->getName();
    }

    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    public function setChunkSize(int $chunkSize): void
    {
        $this->chunkSize = $chunkSize;
    }
}
