<?php

namespace my127\FSM\Runner;

use Exception;
use my127\FSM\Context;
use my127\FSM\State\State;
use my127\FSM\State\StateException;
use my127\FSM\Stateful;
use my127\FSM\Transition\Transition;

class StepRunner implements Runner
{
    /**
     * Context.
     *
     * @var Stateful
     */
    private $context;

    /**
     * Step Runner.
     *
     * A basic machine runner where each call to input correlates to a single
     * transition in the machine.
     *
     * @param Stateful $context
     */
    public function __construct(State $initialState, Stateful $context = null)
    {
        $this->context = $context ?: new Context();
        $this->context->setCurrentState($initialState);
    }

    /**
     * Get Context.
     *
     * @return Stateful
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set Context.
     *
     * @return void
     */
    public function setContext(Stateful $context)
    {
        $this->context = $context;
    }

    /**
     * Transitions available from current state.
     *
     * @return Transition[]
     */
    public function getTransitions()
    {
        return $this->context->getCurrentState()->getTransitions();
    }

    /**
     * Advance.
     *
     * Attempt to advance the machine with the given input, optionally try
     * and follow the exact path as given by the transition.
     *
     * @param mixed      $input
     * @param Transition $transition Take this path, otherwise try all paths
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function apply($input = null, Transition $transition = null)
    {
        $state = $this->context->getCurrentState();

        if ($transition === null && !($transition = $this->can($input))) {
            throw new StateException(sprintf('No transition from %s accepted %s', (string) $state, $input));
        }

        return $transition->apply($input, $this->context, $this);
    }

    /**
     * Can Advance.
     *
     * Determine if the machine can be advanced, if so return the
     * valid transition.
     *
     * Usage :-
     *
     * can(Input)
     * can(Input, Transition)
     *
     * @param mixed      $input      Input against which transition(s) will be tested
     * @param Transition $transition If specified only this transition will be tested
     *
     * @return Transition|false
     *
     * @throws Exception
     */
    public function can($input, $transition = null)
    {
        $state = $this->context->getCurrentState();

        if (!($transition instanceof Transition)) {
            foreach ($state->getTransitions() as $candidate) {
                if (!$candidate->can($input, $this->context, $this)) {
                    continue;
                }

                return $candidate;
            }

            return false;
        }

        return $transition->can($input, $this->context, $this) ? $transition : false;
    }

    /**
     * Get Lambda.
     *
     * @return callable
     */
    public function getLambda()
    {
        return [$this, 'apply'];
    }

    /**
     * Input.
     *
     * @param $input
     *
     * @return mixed
     */
    public function input($input)
    {
        return $this->apply($input);
    }

    /**
     * Alias of Input.
     *
     * @param $input
     *
     * @return mixed
     */
    public function __invoke($input)
    {
        return $this->apply($input);
    }

    /**
     * Get Current State.
     *
     * @return State
     */
    public function getCurrentState()
    {
        return $this->context->getCurrentState();
    }
}
