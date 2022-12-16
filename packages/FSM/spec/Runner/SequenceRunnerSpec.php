<?php

namespace spec\my127\FSM\Runner;

use my127\FSM\Runner\SequenceRunner;
use my127\FSM\State\State;
use my127\FSM\Stateful;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SequenceRunnerSpec extends ObjectBehavior
{
    function let(State $initialState, Stateful $context)
    {
        $this->beConstructedWith($initialState, $context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SequenceRunner::class);
    }

    function it_holds_the_context_it_was_constructed_with(Stateful $context)
    {
        $this->getContext()->shouldReturn($context);
    }
}
