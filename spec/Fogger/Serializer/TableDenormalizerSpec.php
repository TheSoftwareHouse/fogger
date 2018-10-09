<?php

namespace spec\App\Fogger\Serializer;

use App\Fogger\Recipe\StrategyDefinition;
use App\Fogger\Recipe\Table;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TableDenormalizerSpec extends ObjectBehavior
{
    function let(DenormalizerInterface $denormalizer)
    {
        $this->setDenormalizer($denormalizer);
    }

    function it_supports_only_denormalization_of_recipe_table_class_only_from_json()
    {
        $this->supportsDenormalization(Argument::any(), Table::class, 'json')->shouldReturn(true);
        $this->supportsDenormalization(Argument::any(), Table::class, Argument::any())->shouldReturn(false);
        $this->supportsDenormalization(Argument::any(), 'WrongClassName')->shouldReturn(false);
    }

    function it_denormalizes_table_without_masks_specified()
    {
        $data = ['name' => 'table', 'chunkSize' => 10, 'sortBy' => null, 'subset' => 'noSubset'];
        $table = new Table('table', 10, null, new StrategyDefinition('noSubset'));

        $this->denormalize($data, Table::class, 'json')->shouldBeLike($table);
    }

    function it_denormalizes_table_with_subset_strategy_specified(DenormalizerInterface $denormalizer)
    {
        $data = ['name' => 'table', 'chunkSize' => 10, 'sortBy' => null, 'subset' => 'subset'];
        $subsetStrategy = new StrategyDefinition('subset');
        $denormalizer->denormalize($data['subset'], StrategyDefinition::class, 'json', [])->willReturn($subsetStrategy);
        $table = new Table('table', 10, null, $subsetStrategy);

        $this->denormalize($data, Table::class, 'json')->shouldBeLike($table);
    }

    function it_denormalizes_table_with_masks_specified()
    {
        $data = [
            'name' => 'table',
            'chunkSize' => 10,
            'subset' => 'noSubset',
            'sortBy' => null,
            'masks' => [
                'column' => ['name' => 'mask', 'options' => []],
                'other' => ['name' => 'otherMask', 'options' => ['option' => 'value']],
            ],
        ];
        $table = new Table('table', 10, null, new StrategyDefinition('noSubset'));
        $table->addMask('column', new StrategyDefinition('mask'));
        $table->addMask('other', new StrategyDefinition('otherMask', ['option' => 'value']));

        $this->denormalize($data, Table::class, 'json')->shouldBeLike($table);
    }
}
