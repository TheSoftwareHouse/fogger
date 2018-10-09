<?php

namespace App\Fogger\Mask;

final class StarifyMask extends AbstractMask
{
    public function apply(?string $value, array $options = []): ?string
    {
        if (null === $value) {
            return $value;
        }

        return str_repeat('*', $options['length'] ?? 10);
    }

    protected function getMaskName(): string
    {
        return 'starify';
    }
}
