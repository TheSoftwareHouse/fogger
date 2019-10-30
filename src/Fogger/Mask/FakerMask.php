<?php

namespace App\Fogger\Mask;

use Faker\Generator;
use Psr\Cache\CacheItemPoolInterface;

final class FakerMask extends AbstractCachedMask
{
    const DEFAULT_METHOD = 'email';

    private $generator;

    public function __construct(Generator $generator, CacheItemPoolInterface $cache)
    {
        $this->generator = $generator;

        parent::__construct($cache);
    }

    public function getSubstitution(array $options = []): ?string
    {
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
        return 'faker';
    }
}
