<?php

namespace spec\my127\FSM\State;

use my127\FSM\Transition\DefaultTransition;
use my127\FSM\State\DefaultState;
use my127\FSM\State\State;
use my127\FSM\State\StateVisitor;
use my127\FSM\Transition\Transition;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DefaultStateSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('State A');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DefaultState::class);
    }

    function it_is_of_type_normal_by_default()
    {
        $this->isNormal()->shouldBe(true);
        $this->isInitial()->shouldBe(false);
        $this->isTerminal()->shouldBe(false);
    }

    function it_can_have_its_type_changed()
    {
        $this->setType(State::TYPE_INITIAL);
        $this->isInitial()->shouldBe(true);
        $this->isNormal()->shouldBe(false);
        $this->isTerminal()->shouldBe(false);

        $this->setType(State::TYPE_NORMAL);
        $this->isInitial()->shouldBe(false);
        $this->isNormal()->shouldBe(true);
        $this->isTerminal()->shouldBe(false);

        $this->setType(State::TYPE_TERMINAL);
        $this->isInitial()->shouldBe(false);
        $this->isNormal()->shouldBe(false);
        $this->isTerminal()->shouldBe(true);
    }

    function it_adds_transition_as_is_when_it_is_of_type_transition(Transition $transition)
    {
        $transitions = [$transition];

        $this->addTransition($transition);
        $this->getTransitions()->shouldReturn($transitions);
    }

    function it_constructs_transition_when_not_of_type_transition()
    {
        $transitions = [new DefaultTransition('Transition A', new DefaultState('S2'), null, null)];

        $this->addTransition('Transition A', new DefaultState('S2'), null, null);
        $this->getTransitions()->shouldBeLike($transitions);
    }

    function it_can_accept_visitor(StateVisitor $visitor)
    {
        $this->accept($visitor);
        $visitor->visit($this->getWrappedObject())->shouldHaveBeenCalled();
    }

    function it_will_pass_visitor_on_to_all_available_transitions(StateVisitor $visitor, Transition $t1, Transition $t2)
    {
        $this->addTransition($t1);
        $this->addTransition($t2);
        $this->accept($visitor);

        $t1->accept($visitor, Argument::any())->shouldHaveBeenCalled();
        $t2->accept($visitor, Argument::any())->shouldHaveBeenCalled();
    }

    function it_provides_label_when_toString_is_called()
    {
        $this->__toString()->shouldReturn('State A');
    }

    function it_can_be_copied()
    {
        $t1 = new DefaultTransition('t1', new DefaultState('S2'));
        $t2 = new DefaultTransition('t2', new DefaultState('S3'));

        $this->addTransition($t1);
        $this->addTransition($t2);

        $expect = new DefaultState('State A');
        $expect->addTransition($t1);
        $expect->addTransition($t2);

        $this->copy()->shouldBeLike($expect);
    }
}
