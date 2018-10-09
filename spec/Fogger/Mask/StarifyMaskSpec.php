<?php

namespace spec\App\Fogger\Mask;

use App\Fogger\Mask\MaskStrategyInterface;
use PhpSpec\ObjectBehavior;

class StarifyMaskSpec extends ObjectBehavior
{
    function it_should_implement_mask_provider_interface()
    {
        $this->shouldHaveType(MaskStrategyInterface::class);
    }

    function it_should_supports_starify()
    {
        $this->supports('starify')->shouldBe(true);
        $this->supports('wrongName')->shouldBe(false);
    }

     function it_should_mask_given_value()
    {
        $this->apply('dummyValue')->shouldBe('**********');
    }

    function it_should_allow_to_specify_how_many_character_should_be_returned()
    {
        $this->apply('dummyValue', ['length' => 2])->shouldBe('**');
    }

    function it_should_ignore_null_value()
    {
        $this->apply(null)->shouldBe(null);
    }
}
