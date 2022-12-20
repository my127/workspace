<?php

namespace my127\FSM;

use my127\FSM\Runner\Runner;
use my127\FSM\Runner\RunnerFactory;
use my127\FSM\State\DefaultState;
use my127\FSM\State\State;
use my127\FSM\State\StateVisitor;
use my127\FSM\State\StateVisitorClient;
use my127\FSM\Transition\DefaultTransition;
use my127\FSM\Transition\DefinitionTransitionBuilder;
use my127\FSM\Transition\Transition;

class Definition implements StateVisitorClient
{
    /**
     * Label.
     *
     * @var string
     */
    private $label;

    /**
     * States.
     *
     * @var State[]
     */
    private $states = [];

    /**
     * Initial State.
     *
     * @var State
     */
    private $initialState = null;

    /**
     * StepRunner Factory.
     *
     * @var RunnerFactory
     */
    private $runnerFactory;

    /**
     * Definition.
     *
     * @param string        $label
     * @param RunnerFactory $runnerFactory
     */
    public function __construct($label, RunnerFactory $runnerFactory = null)
    {
        $this->label = $label;
        $this->runnerFactory = $runnerFactory;
    }

    /**
     * Add State.
     *
     * @param State|string $state
     * @param string       $type
     *
     * @throws \Exception
     */
    public function addState($state, $type = State::TYPE_NORMAL)
    {
        if (!($state instanceof State)) {
            $state = new DefaultState($state, $type);
        }

        $label = (string) $state;

        if (isset($this->states[$label])) {
            throw new \Exception(sprintf('State %s already exists.', $label));
        }

        if (is_null($this->initialState)) {
            $this->initialState = $state;
        }

        $this->states[$label] = $state;
    }

    /**
     * Get State.
     *
     * @param string $state
     * @param string $type
     *
     * @return State
     */
    public function getState($state, $type = State::TYPE_NORMAL)
    {
        if (!isset($this->states[$state])) {
            $this->addState($state, $type);
        }

        return $this->states[$state];
    }

    /**
     * Add Transition.
     *
     * Usage :-
     *
     * addTransition(Transition, From)
     * addTransition(Label, From, To [,Guard [,Action]])
     *
     * @param string|Transition $transition
     * @param string|State      $from
     * @param string|State      $to
     * @param callable          $guard
     * @param string|callable   $action
     *
     * @return void
     */
    public function addTransition($transition, $from, $to = null, callable $guard = null, $action = null)
    {
        if (!($from instanceof State)) {
            $from = $this->getState($from);
        }

        if (!($transition instanceof Transition)) {
            $transition = new DefaultTransition(
                $transition,
                ($to instanceof State) ? $to : $this->getState($to),
                $guard,
                $action
            );
        }

        $from->addTransition($transition);
    }

    /**
     * Create Transition.
     *
     * Fluid counterpart to addTransition
     *
     * @param string|State $state
     * @param string       $type
     *
     * @return DefinitionTransitionBuilder
     */
    public function from($state, $type = State::TYPE_NORMAL)
    {
        return (new DefinitionTransitionBuilder($this))->from($state, $type);
    }

    /**
     * Accept Visitor.
     *
     * @return void
     */
    public function accept(StateVisitor $visitor)
    {
        $visited = [];

        foreach ($this->states as $state) {
            $state->accept($visitor, $visited);
        }
    }

    /**
     * Set Initial State.
     *
     * @return void
     *
     * TODO: update to allow strings as well
     */
    public function setInitialState(State $initial)
    {
        $this->initialState = $initial;
    }

    /**
     * Get Initial State.
     *
     * @return State
     *
     * @throws \Exception
     */
    public function getInitialState()
    {
        if (is_null($this->initialState)) {
            throw new \Exception();
        }

        return $this->initialState;
    }

    /**
     * Build as FSM.
     *
     * @param Stateful $context
     *
     * @return Runner
     *
     * @throws \Exception
     */
    public function toFSM(Stateful $context = null)
    {
        return $this->getRunnerFactory()->buildFSM($this->getInitialState(), $context);
    }

    /**
     * Build as Lambda.
     *
     * @param Stateful $context
     *
     * @return callable
     */
    public function toLambda(Stateful $context = null)
    {
        return $this->toFSM($context)->getLambda();
    }

    /**
     * Perform a deep clone.
     */
    public function __clone()
    {
        $visited = [];

        foreach ($this->states as $k => $state) {
            $this->states[$k] = $state->copy($visited);
        }

        if ($this->initialState instanceof State) {
            $this->setInitialState($visited[spl_object_hash($this->initialState)]);
        }
    }

    /**
     * Get StepRunner Factory.
     *
     * @return RunnerFactory
     */
    private function getRunnerFactory()
    {
        if (is_null($this->runnerFactory)) {
            $this->runnerFactory = new RunnerFactory();
        }

        return $this->runnerFactory;
    }
}
