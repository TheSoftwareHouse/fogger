<?php

namespace App\Config\Serializer\Mongo;

use App\Config\Model\Mongo\CollectionConfig;
use App\Config\Model\Mongo\Config;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ConfigDenormalizer implements DenormalizerInterface
{
    use DenormalizerAwareTrait;

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == Config::class;
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $config = new Config(
            $data['source'] ?? 'source',
            $data['target'] ?? 'target',
            $data['chunkSize'] ?? 100,
            $data['suffix'] ?? ''
        );

        foreach ($data['collections'] ?? [] as $key => $collection) {
            /** @var CollectionConfig $collectionConfig */
            $collectionConfig = $this->denormalizer->denormalize(
                $collection,
                CollectionConfig::class,
                $format,
                $context
            );
            $config->addCollection($key, $collectionConfig);
        }

        return $config;
    }
}
