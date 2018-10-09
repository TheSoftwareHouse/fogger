<?php

namespace spec\App\Config\Serializer;

use App\Config\Model\Config;
use App\Config\Model\TableConfig;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ConfigDenormalizerSpec extends ObjectBehavior
{
    function let(DenormalizerInterface $denormalizer)
    {
        $this->setDenormalizer($denormalizer);
    }

    function it_supports_only_denormalization_of_config_class()
    {
        $this->supportsDenormalization(Argument::any(), Config::class)->shouldReturn(true);
        $this->supportsDenormalization(Argument::any(), 'WrongClassName')->shouldReturn(false);
    }

    function it_denormalizes_empty_config()
    {
        $data = ['tables' => []];
        $config = new Config();

        $this->denormalize($data, Config::class)->shouldBeLike($config);
    }

    function it_denormalizes_config_with_tables(DenormalizerInterface $denormalizer)
    {
        $data = [
            'tables' => [
                'table' => [],
            ],
        ];

        $config = new Config();
        foreach ($data['tables'] as $key => $table) {
            $tableConfig = new TableConfig();
            $config->addTable($key, $tableConfig);
            $denormalizer->denormalize($table, TableConfig::class, Argument::any(), Argument::any())
                ->willReturn($tableConfig);
        }

        $this->denormalize($data, TableConfig::class)->shouldBeLike($config);
    }
}
