<?php

namespace App\Fogger\Mask;

use Psr\Cache\CacheItemPoolInterface;

abstract class AbstractCachedMask extends AbstractMask
{
    /**
     * @var CacheItemPoolInterface
     */
    protected $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    abstract protected function getSubstitution(array $options = []): ?string;

    /**
     * @param null|string $value
     * @param array $options
     * @return null|string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function apply(?string $value, array $options = []): ?string
    {
        if (null === $value) {
            return $value;
        }

        $originalValueCacheItem = $this->cache->getItem(md5($value));
        if ($originalValueCacheItem->isHit()) {
            return $originalValueCacheItem->get();
        }

        do {
            $substitution = $this->getSubstitution($options);
            $substitutionCacheItem = $this->cache->getItem(md5($substitution));
        } while ($substitutionCacheItem->isHit());
        $this->cache->save($substitutionCacheItem);

        $originalValueCacheItem->set($substitution);
        $this->cache->save($originalValueCacheItem);

        return $substitution;
    }
}
