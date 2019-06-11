<?php
/**
 * Created by PhpStorm.
 * User: suro
 * Date: 05/06/2019
 * Time: 22:29
 */

namespace App\Config\Serializer\Mongo;

use App\Config\Model\Mongo\CollectionConfig;
use App\Config\Model\Mongo\PathConfig;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CollectionDenormalizer implements DenormalizerInterface
{
    use DenormalizerAwareTrait;

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == CollectionConfig::class;
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $collection = new CollectionConfig($data['limit'] ?? 0);
        foreach ($data['paths'] ?? [] as $key => $path) {
            /** @var PathConfig $pathConfig */
            $pathConfig = $this->denormalizer->denormalize($path, PathConfig::class, $format, $context);
            $collection->addPath($key, $pathConfig);
        }

        return $collection;
    }
}
