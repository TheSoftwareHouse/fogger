<?php

namespace spec\App\Config;

use App\Config\Model\ColumnConfig;
use App\Config\StrategyExtractor;
use Doctrine\DBAL\Schema\Column;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ColumnConfigFactorySpec extends ObjectBehavior
{
    function let(StrategyExtractor $extractor)
    {
        $this->beConstructedWith($extractor);
    }

    function it_returns_column_config_created_by_extractor(Column $column, StrategyExtractor $extractor)
    {
        $instance = new ColumnConfig('none');
        $extractor->extract(Argument::any())->willReturn($instance);

        $this->createFromDBALColumn($column)->shouldBe($instance);
    }
}
