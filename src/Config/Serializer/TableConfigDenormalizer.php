<?php

namespace App\Config\Serializer;

use App\Config\Model\ColumnConfig;
use App\Config\Model\TableConfig;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TableConfigDenormalizer implements DenormalizerInterface
{
    use DenormalizerAwareTrait;

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == TableConfig::class;
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $table = new TableConfig();
        foreach ($data['columns'] ?? [] as $key => $column) {
            /** @var ColumnConfig $columnConfig */
            $columnConfig = $this->denormalizer->denormalize($column, ColumnConfig::class, $format, $context);
            $table->addColumn($key, $columnConfig);
        }
        if (isset($data['subsetStrategy'])) {
            $table->setSubsetStrategy($data['subsetStrategy'], $data['subsetOptions'] ?? []);
        }

        return $table;
    }
}
