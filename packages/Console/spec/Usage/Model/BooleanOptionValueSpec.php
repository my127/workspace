<?php

namespace spec\my127\Console\Usage\Model;

use my127\Console\Usage\Model\BooleanOptionValue;
use PhpSpec\ObjectBehavior;

class BooleanOptionValueSpec extends ObjectBehavior
{
    function it_can_get_the_value()
    {
        $this->beConstructedThrough('create', [true]);
        $this->value()->shouldBe(true);
    }

    function it_can_get_the_value_varied()
    {
        $this->beConstructedThrough('create', [false]);
        $this->value()->shouldBe(false);
    }

    function it_is_equal_to_same_value()
    {
        $comparisonTo = BooleanOptionValue::create(true);

        $this->beConstructedThrough('create', [true]);
        $this->equals($comparisonTo)->shouldBe(true);
    }

    function it_is_not_equal_to_different_value()
    {
        $comparisonTo = BooleanOptionValue::create(true);

        $this->beConstructedThrough('create', [false]);
        $this->equals($comparisonTo)->shouldBe(false);
    }
}
