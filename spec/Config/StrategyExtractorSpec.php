<?php

namespace spec\App\Config;

use App\Config\Model\ColumnConfig;
use PhpSpec\ObjectBehavior;

class StrategyExtractorSpec extends ObjectBehavior
{
    function it_returns_column_config_with_none_strategy_for_empty_comment()
    {
        $this->extract('')->shouldBeLike(new ColumnConfig('none'));
    }

    function it_returns_column_config_with_none_strategy_for_comment_not_maching_template()
    {
        $this->extract('some comment')->shouldBeLike(new ColumnConfig('none'));
    }

    function it_returns_column_config_with_proper_strategy_for_comment_maching_template()
    {
        $this->extract('fogger::strategy')->shouldBeLike(new ColumnConfig('strategy'));
    }

    function it_returns_column_config_with_proper_strategy_for_comment_maching_template_with_options()
    {
        $this->extract('fogger::strategy{"key": "value"}')
            ->shouldBeLike(new ColumnConfig('strategy', ['key' => 'value']));
    }
}
