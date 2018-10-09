<?php

namespace App\Fogger\Subset;

use App\Fogger\Subset\Exception\RequiredOptionMissingException;

abstract class AbstractSubset implements SubsetStrategyInterface
{
    abstract protected function getSubsetStrategyName(): string;

    public function supports(string $name): bool
    {
        return $name === $this->getSubsetStrategyName();
    }

    /**
     * @param array $options
     * @param $option
     * @throws RequiredOptionMissingException
     */
    protected function ensureOptionIsSet(array $options, $option)
    {
        if (!isset($options[$option])) {
            throw new RequiredOptionMissingException(
                sprintf(
                    'Strategy %s requires option "%s" to be set',
                    $this->getSubsetStrategyName(),
                    $option
                )
            );
        }
    }
}
