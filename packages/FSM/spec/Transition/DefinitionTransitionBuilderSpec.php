<?php

namespace spec\my127\FSM\Transition;

use Exception;
use my127\FSM\State\DefaultState;
use my127\FSM\Transition\DefaultTransition;
use my127\FSM\Definition;
use my127\FSM\Transition\DefinitionTransitionBuilder;
use my127\FSM\State\State;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DefinitionTransitionBuilderSpec extends ObjectBehavior
{
    function let(Definition $definition)
    {
        $this->beConstructedWith($definition);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DefinitionTransitionBuilder::class);
    }

    function it_throws_exception_when_from_state_is_missing()
    {
        $this->shouldThrow(new Exception('Missing From State'))->duringDone();
    }

    function it_throws_exception_when_target_state_is_missing(State $from)
    {
        $this->from($from);
        $this->shouldThrow(new Exception('Missing To State'))->duringDone();
    }

    function it_builds_default_transition_based_on_given_details(State $from)
    {
        $to     = new DefaultState('S2');
        $guard  = function () {
            return true; 
        };
        $action = function () {
            return 'applied'; 
        };
        $expect = new DefaultTransition('t1', $to, $guard, $action);

        $this
            ->from($from)
            ->to($to)
            ->when('t1', $guard)
            ->then($action);

        $this->done();

        $from->addTransition($expect)->shouldHaveBeenCalled();
    }

    function it_returns_definition_after_successful_call_to_done(Definition $definition, State $from, State $to)
    {
        $this
            ->from($from)
            ->to($to)
            ->when('t1')
            ->then('applied');

        $this->done()->shouldReturn($definition);
    }

    function it_allows_the_then_step_to_be_skipped(Definition $definition, State $from, State $to)
    {
        $this
            ->from($from)
            ->to($to)
            ->when('t1');

        $this->done()->shouldReturn($definition);
    }
}
