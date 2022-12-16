<?php

namespace spec\my127\Console\Usage\Model;

use my127\Console\Usage\Model\StringOptionValue;
use PhpSpec\ObjectBehavior;

class StringOptionValueSpec extends ObjectBehavior
{
    function it_can_get_the_value()
    {
        $this->beConstructedThrough('create', ['testValue']);
        $this->value()->shouldBe('testValue');
    }

    function it_can_get_the_value_varied()
    {
        $this->beConstructedThrough('create', ['testValue2']);
        $this->value()->shouldBe('testValue2');
    }

    function it_is_equal_to_same_value()
    {
        $comparisonTo = StringOptionValue::create('testValue');

        $this->beConstructedThrough('create', ['testValue']);
        $this->equals($comparisonTo)->shouldBe(true);
    }

    function it_is_not_equal_to_different_value()
    {
        $comparisonTo = StringOptionValue::create('testValue');

        $this->beConstructedThrough('create', ['notTestValue']);
        $this->equals($comparisonTo)->shouldBe(false);
    }
}
