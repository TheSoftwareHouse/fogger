<?php

namespace App\Fogger\Serializer;

use App\Fogger\Recipe\Mongo\Collection;
use App\Fogger\Recipe\StrategyDefinition;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CollectionDenormalizer implements DenormalizerInterface
{
    use DenormalizerAwareTrait;

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $format == 'json' && $type == Collection::class;
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $collection = new Collection($data['name']);
        foreach ($data['masks'] ?? [] as $key => $mask) {
            $collection->addMask($key, new StrategyDefinition($mask['name'], $mask['options']));
        }

        return $collection;
    }
}
