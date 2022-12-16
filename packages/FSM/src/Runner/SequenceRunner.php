<?php

namespace my127\FSM\Runner;

use Exception;
use my127\FSM\Context;
use my127\FSM\State\State;
use my127\FSM\State\StateException;
use my127\FSM\Stateful;
use my127\FSM\Transition\Transition;
use Traversable;

class SequenceRunner implements Runner
{
    /**
     * @var Stateful
     */
    private $context;

    /**
     * @var Stateful
     */
    private $originalContext;

    /**
     * Sequence Runner.
     *
     * Treats input as a sequence of symbols, in turn each is passed to the machine
     * taking the first accepting path at each state.
     *
     * @param Stateful $context
     */
    public function __construct(State $initialState, Stateful $context = null)
    {
        if ($context === null) {
            $context = new Context();
        }

        $context->setCurrentState($initialState);

        $this->originalContext = clone $this->context = $context;
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
     * Input.
     *
     * @param array|Traversable $input
     *
     * @return mixed[]|false
     */
    public function input($input)
    {
        $this->context = clone $this->originalContext;

        $output = [];

        foreach ($input as $symbol) {
            if (!($transition = $this->can($symbol))) {
                return false;
            }

            $output[] = $this->apply($symbol, $transition);
        }

        if (!$this->context->getCurrentState()->isTerminal()) {
            return false;
        }

        return $output;
    }

    /**
     * @return callable
     */
    public function getLambda()
    {
        return [$this, 'input'];
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
    private function apply($input = null, Transition $transition = null)
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
    private function can($input, $transition = null)
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
     * Alias of input.
     *
     * @param $input
     *
     * @return mixed
     */
    public function __invoke($input)
    {
        return $this->input($input);
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
