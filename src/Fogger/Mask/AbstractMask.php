<?php

namespace App\Fogger\Mask;

abstract class AbstractMask implements MaskStrategyInterface
{
    abstract protected function getMaskName(): string;

    public function supports(string $name): bool
    {
        if ($name === $this->getMaskName()) {
            return true;
        }

        return false;
    }
}
