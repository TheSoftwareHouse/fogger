<?php

namespace App\Config\Serializer;

use App\Config\Model\Config;
use App\Config\Model\TableConfig;
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
        $config = new Config();

        foreach ($data['tables'] ?? [] as $key => $table) {
            /** @var TableConfig $tableConfig */
            $tableConfig = $this->denormalizer->denormalize($table, TableConfig::class, $format, $context);
            $config->addTable($key, $tableConfig);
        }
        $config->setExcludes($data['excludes'] ?? []);

        return $config;
    }
}
