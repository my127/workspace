<?php

namespace spec\my127\FSM;

use my127\FSM\Context;
use my127\FSM\State\State;
use my127\FSM\Stateful;
use PhpSpec\ObjectBehavior;

class ContextSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Context::class);
    }

    function it_is_stateful()
    {
        $this->shouldHaveType(Stateful::class);
    }

    function it_holds_reference_to_set_state(State $state)
    {
        $this->setCurrentState($state);
        $this->getCurrentState()->shouldBe($state);
    }
}
