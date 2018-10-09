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
        $parameters = $options['parameters'] ?? [];
        $result = $this->generator->$method(...$parameters);

        if (is_array($result)) {
            return implode(' ', $result);
        } else if ($result instanceof \DateTime) {
            return $result->format("Y-m-d H:i:s");
        }

        return $result;
    }

    protected function getMaskName(): string
    {
        return 'faker';
    }
}
