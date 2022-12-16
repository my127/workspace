<?php

namespace spec\my127\FSM;

use my127\FSM\Definition;
use my127\FSM\State\DefaultState;
use my127\FSM\Transition\DefaultTransition;
use my127\FSM\State\State;
use my127\FSM\State\StateVisitor;
use my127\FSM\Transition\Transition;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DefinitionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Default');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Definition::class);
    }

    function it_can_provide_states_by_state_label(State $state)
    {
        $state->__toString()->willReturn('S1');
        $this->addState($state);
        $this->getState('S1')->shouldReturn($state);
    }

    function it_makes_the_first_added_state_the_initial_state(State $state)
    {
        $state->__toString()->willReturn('S1');
        $this->addState($state);
        $this->getInitialState()->shouldReturn($state);
    }

    function it_creates_default_state_if_no_state_by_label_exists()
    {
        $this->getState('S1')->shouldBeLike(new DefaultState('S1'));
    }

    function it_can_add_transition_to_state(State $s1, Transition $t1)
    {
        $s1->__toString()->willReturn('S1');
        $s1->addTransition(Argument::any())->willReturn(null);
        $this->addState($s1);
        $this->addTransition($t1, $s1);

        $s1->addTransition($t1)->shouldHaveBeenCalled();
    }

    function it_can_construct_default_transition_to_add_to_state(State $s1)
    {
        $s1->__toString()->willReturn('S1');
        $s2 = new DefaultState('S2');
        $s1->addTransition(Argument::any())->willReturn(null);
        $this->addState($s1);
        $this->addTransition('t1', $s1, $s2);

        $expect = new DefaultTransition('t1', $s2);

        $s1->addTransition(
            Argument::that(
                function ($actual) use ($expect) {
                    return $actual == $expect;
                }
            )
        )->shouldHaveBeenCalled();
    }

    function it_can_accept_state_visitor(State $s1, StateVisitor $stateVisitor)
    {
        $s1->__toString()->willReturn('S1');
        $s1->accept(Argument::cetera())->willReturn(null);
        $this->addState($s1);

        $this->accept($stateVisitor);
        $visited = [];
        $s1->accept($stateVisitor, $visited)->shouldHaveBeenCalled();
    }

    function it_can_have_the_initial_state_changed(State $s1, State $s2)
    {
        $s1->__toString()->willReturn('S1');
        $s2->__toString()->willReturn('S2');

        $this->addState($s1);
        $this->addState($s2);

        $this->setInitialState($s2);
        $this->getInitialState()->shouldReturn($s2);
    }
}
