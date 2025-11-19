<?php

namespace my127\FSM\State;

use my127\FSM\Definition;
use my127\FSM\Transition\DefaultTransition;

class StateTransitionBuilder
{
    /**
     * Label.
     *
     * @var string
     */
    private $label;

    /**
     * From.
     *
     * @var State
     */
    private $from;

    /**
     * To.
     *
     * @var State
     */
    private $to;

    /**
     * Guard.
     *
     * @var callable
     */
    private $guard;

    /**
     * Action.
     *
     * @var callable
     */
    private $action;

    /**
     * State Transition Builder.
     */
    public function __construct(State $from)
    {
        $this->from = $from;
    }

    /**
     * To.
     *
     * @param State $state
     *
     * @return $this
     */
    public function to($state)
    {
        $this->to = $state;

        return $this;
    }

    /**
     * When.
     *
     * @param string $label
     *
     * @return $this
     */
    public function when($label, ?callable $guard = null)
    {
        $this->label = $label;
        $this->guard = $guard;

        return $this;
    }

    /**
     * Then.
     *
     * @param string $action
     *
     * @return $this
     */
    public function then($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Done.
     *
     * @return Definition
     *
     * @throws \Exception
     */
    public function done()
    {
        if ($this->to === null) {
            throw new \Exception('Missing To State');
        }

        if ($this->label == null) {
            throw new \Exception('Missing Label');
        }

        $this->from->addTransition(new DefaultTransition($this->label, $this->to, $this->guard, $this->action));

        return $this->from;
    }
}
