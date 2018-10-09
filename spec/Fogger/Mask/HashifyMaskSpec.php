<?php

namespace spec\App\Fogger\Mask;

use App\Fogger\Mask\HashifyMask;
use App\Fogger\Mask\MaskStrategyInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HashifyMaskSpec extends ObjectBehavior
{
    function it_should_implement_mask_provider_interface()
    {
        $this->shouldHaveType(MaskStrategyInterface::class);
    }

    function it_should_supports_hashify()
    {
        $this->supports('hashify')->shouldBe(true);
        $this->supports('wrongName')->shouldBe(false);
    }

    function it_should_mask_given_value()
    {
        $dummyValue = 'dummyValue';
        $this->apply($dummyValue)->shouldBe(md5($dummyValue));
    }

    function it_should_allow_to_specify_template_which_should_be_use_to_generate_response()
    {
        $template = 'template %s template';
        $dummyValue = 'dummyValue';
        $this->apply($dummyValue, ['template' => $template])->shouldBe(sprintf($template, md5($dummyValue)));
    }

    function it_should_ignore_null_value()
    {
        $this->apply(null)->shouldBe(null);
    }

}
