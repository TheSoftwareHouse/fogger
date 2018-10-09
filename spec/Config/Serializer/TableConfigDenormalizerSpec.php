<?php

namespace spec\App\Config\Serializer;

use App\Config\Model\ColumnConfig;
use App\Config\Model\TableConfig;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TableConfigDenormalizerSpec extends ObjectBehavior
{
    function let(DenormalizerInterface $denormalizer)
    {
        $this->setDenormalizer($denormalizer);
    }

    function it_supports_only_denormalization_of_table_config_class()
    {
        $this->supportsDenormalization(Argument::any(), TableConfig::class)->shouldReturn(true);
        $this->supportsDenormalization(Argument::any(), 'WrongClassName')->shouldReturn(false);
    }

    function it_denormalizes_data_with_empty_columns_array_and_no_subset_into_table_config_object()
    {
        $data = ['columns' => []];
        $table = new TableConfig();
        $this->denormalize($data, TableConfig::class)->shouldBeLike($table);
    }

    function it_denormalizes_data_with_empty_columns_array_and_subset_into_table_config_object()
    {
        $data = ['columns' => [], 'subsetStrategy' => 'none', 'subsetOptions' => []];
        $table = new TableConfig();
        $table->setSubsetStrategy($data['subsetStrategy'], $data['subsetOptions']);

        $this->denormalize($data, TableConfig::class)->shouldBeLike($table);
    }

    function it_denormalizes_data_with_columns_array(DenormalizerInterface $denormalizer)
    {
        $data = [
            'columns' => [
                'column' => [],
            ],
        ];

        $table = new TableConfig();

        foreach ($data['columns'] as $key => $col) {
            $columnConfig = new ColumnConfig();
            $table->addColumn($key, $columnConfig);
            $denormalizer->denormalize($col, ColumnConfig::class, Argument::any(), Argument::any())->willReturn(
                $columnConfig
            );
        }

        $this->denormalize($data, TableConfig::class)->shouldBeLike($table);
    }
}
