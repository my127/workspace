<?php

namespace my127\Workspace\FSM\State;

use Exception;
use my127\Workspace\FSM\Transition\DefaultTransition;
use my127\Workspace\FSM\Definition;
use my127\Workspace\FSM\State\State;
use my127\Workspace\FSM\Definition;

class StateTransitionBuilder
{
    /**
     * Label
     *
     * @var string
     */
    private $label = null;

    /**
     * From
     *
     * @var State
     */
    private $from;

    /**
     * To
     *
     * @var State
     */
    private $to = null;

    /**
     * Guard
     *
     * @var callable
     */
    private $guard = null;

    /**
     * Action
     *
     * @var callable
     */
    private $action = null;

    /**
     * State Transition Builder
     *
     * @param State $from
     */
    public function __construct(State $from)
    {
        $this->from = $from;
    }

    /**
     * To
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
     * When
     *
     * @param string   $label
     * @param callable $guard
     *
     * @return $this
     */
    public function when($label, callable $guard = null)
    {
        $this->label = $label;
        $this->guard = $guard;

        return $this;
    }

    /**
     * Then
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
     * Done
     *
     * @return Definition
     * @throws Exception
     */
    public function done()
    {
        if ($this->to === null) {
            throw new Exception('Missing To State');
        }

        if ($this->label == null) {
            throw new Exception('Missing Label');
        }

        $this->from->addTransition(new DefaultTransition($this->label, $this->to, $this->guard, $this->action));

        return $this->from;
    }
}
