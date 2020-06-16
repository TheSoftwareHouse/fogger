<?php

namespace App\Fogger\Mask;

use Faker\Generator;

final class FakerUnstoredMask extends AbstractMask
{
    const DEFAULT_METHOD = 'email';

    private $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function apply(?string $value, array $options = []): ?string
    {
        if ((null === $value) or ("" === $value )) {
            return $value;
        }

        $method = $options['method'] ?? self::DEFAULT_METHOD;
        $arguments = $options['arguments'] ?? [];
        $modifier = $options['modifier'] ?? null;
        $modifierArguments = $options['modifierArguments'] ?? [];

        $generator = $this->generator;

        if ('optional' === $modifier) {
            $generator = $generator->optional(...$modifierArguments);
        }

        $result = $generator->$method(...$arguments);

        if (is_array($result)) {
            $result = implode(' ', $result);
        } elseif ($result instanceof \DateTime) {
            $result = $result->format("Y-m-d H:i:s");
        }

        return $result;
    }

    protected function getMaskName(): string
    {
        return 'fakerunstored';
    }
}
