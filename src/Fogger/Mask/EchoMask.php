<?php

namespace App\Fogger\Mask;

final class EchoMask extends AbstractMask
{
    public function apply(?string $value, array $options = []): ?string
    {
        if (null === $value) {
            return $value;
        }

        return $options["text"];
    }

    protected function getMaskName(): string
    {
        return 'echo';
    }
}
