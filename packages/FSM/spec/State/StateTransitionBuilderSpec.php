<?php

namespace spec\my127\FSM\State;

use Exception;
use my127\FSM\State\DefaultState;
use my127\FSM\Transition\DefaultTransition;
use my127\FSM\State\State;
use my127\FSM\State\StateTransitionBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StateTransitionBuilderSpec extends ObjectBehavior
{
    function let(State $from)
    {
        $this->beConstructedWith($from);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(StateTransitionBuilder::class);
    }

    function it_throws_exception_when_target_state_is_missing()
    {
        $this->shouldThrow(new Exception('Missing To State'))->duringDone();
    }

    function it_throws_exception_when_label_is_missing(State $to)
    {
        $this->to($to);
        $this->shouldThrow(new Exception('Missing Label'))->duringDone();
    }

    function it_builds_default_transition_based_on_given_details(State $from)
    {
        $to     = new DefaultState('S1');
        $guard  = function () {
            return true; 
        };
        $action = function () {
            return 'applied'; 
        };
        $expect = new DefaultTransition('t1', $to, $guard, $action);

        $this
            ->to($to)
            ->when('t1', $guard)
            ->then($action);

        $this->done();

        $from->addTransition($expect)->shouldHaveBeenCalled();
    }

    function it_provides_from_state_after_successfully_building_transition(State $from, State $to)
    {
        $this
            ->to($to)
            ->when('t1')
            ->then('applied');

        $this->done()->shouldReturn($from);
    }

    function it_allows_the_then_step_to_be_skipped(State $from, State $to)
    {
        $this
            ->to($to)
            ->when('t1');

        $this->done()->shouldReturn($from);
    }
}
