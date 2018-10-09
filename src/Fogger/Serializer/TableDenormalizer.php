<?php

namespace App\Fogger\Serializer;

use App\Fogger\Recipe\StrategyDefinition;
use App\Fogger\Recipe\Table;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TableDenormalizer implements DenormalizerInterface
{
    use DenormalizerAwareTrait;

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $format == 'json' && $type == Table::class;
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        /** @var StrategyDefinition $subset */
        $subset = $this->denormalizer
            ->denormalize($data['subset'], StrategyDefinition::class, $format, $context);

        $table = new Table(
            $data['name'],
            $data['chunkSize'],
            $data['sortBy'],
            $subset ?? new StrategyDefinition('noSubset')
        );
        foreach ($data['masks'] ?? [] as $key => $mask) {
            $table->addMask($key, new StrategyDefinition($mask['name'], $mask['options']));
        }

        return $table;
    }
}
