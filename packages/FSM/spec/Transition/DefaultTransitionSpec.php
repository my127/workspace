<?php

namespace spec\my127\FSM\Transition;

use my127\FSM\State\DefaultState;
use my127\FSM\Transition\DefaultTransition;
use my127\FSM\Runner\Runner;
use my127\FSM\State\State;
use my127\FSM\Stateful;
use my127\FSM\State\StateVisitor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DefaultTransitionSpec extends ObjectBehavior
{
    function let(State $to)
    {
        $this->beConstructedWith('Transition A', $to);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DefaultTransition::class);
    }

    function it_provides_state_to_be_transitioned_to(State $to)
    {
        $this->getTo()->shouldBe($to);
    }


    function it_allows_the_transition_to_state_to_be_changed(State $newTo)
    {
        $this->setTo($newTo);
        $this->getTo()->shouldBe($newTo);
    }

    function it_can_accept_a_state_visitor(StateVisitor $visitor, State $to)
    {
        $visited = [];

        $this->accept($visitor);
        $to->accept($visitor, $visited)->shouldHaveBeenCalled();
    }

    function it_uses_guard_when_specified_to_check_if_transitioning_is_possible(State $to, Stateful $context, Runner $runner)
    {
        $guard = function ($input) {
            return $input == 'my_token'; 
        };

        $this->beConstructedWith('Transition A', $to, $guard);

        $this->can('my_token', $context, $runner)->shouldBe(true);
        $this->can('not_my_token', $context, $runner)->shouldBe(false);
    }

    function it_compares_input_to_label_when_no_guard_is_specified(State $to, Stateful $context, Runner $runner)
    {
        $this->beConstructedWith('switch_on', $to);

        $this->can('switch_on', $context, $runner)->shouldBe(true);
        $this->can('switch_off', $context, $runner)->shouldBe(false);
    }

    function it_can_transition_the_specified_context_to_the_set_state(State $to, Stateful $context, Runner $runner)
    {
        $to->__toString()->willReturn('To');
        $this->apply('Transition A', $context, $runner);
        $context->setCurrentState($to)->shouldHaveBeenCalled();
    }

    function it_can_be_copied(State $to)
    {
        $visited = [];
        $to->copy($visited)->willReturn(new DefaultState('State A'));

        $this->copy()->shouldBeLike(new DefaultTransition('Transition A', new DefaultState('State A'), null, null));
    }

    function it_provides_label_when_toString_is_called()
    {
        $this->__toString()->shouldBe('Transition A');
    }
}
