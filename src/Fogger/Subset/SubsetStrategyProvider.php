<?php

namespace App\Fogger\Subset;

use App\Fogger\Subset\Exception\UnknownSubsetStrategyException;

class SubsetStrategyProvider
{
    private $subsetStrategies = [];

    public function __construct(iterable $subsetStrategies)
    {
        foreach ($subsetStrategies as $subsetStrategy) {
            $this->addSubsetStrategy($subsetStrategy);
        }
    }

    private function addSubsetStrategy(SubsetStrategyInterface $subsetStrategy)
    {
        $this->subsetStrategies[] = $subsetStrategy;
    }

    /**
     * @param string $name
     * @return SubsetStrategyInterface
     * @throws UnknownSubsetStrategyException
     */
    public function getSubsetStrategy(?string $name = 'noSubset'): SubsetStrategyInterface
    {
        foreach ($this->subsetStrategies as $subsetStrategy) {
            if ($subsetStrategy->supports($name)) {
                return $subsetStrategy;
            }
        }

        throw new UnknownSubsetStrategyException('Unknown subset strategy "'.$name.'".');
    }
}
