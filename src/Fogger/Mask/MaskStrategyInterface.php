<?php

namespace App\Fogger\Mask;

interface MaskStrategyInterface
{
    public function apply(?string $value, array $options = []): ?string;

    public function supports(string $name): bool;
}
