<?php

namespace my127\FSM\Transition;

use my127\FSM\Definition;
use my127\FSM\State\State;

class DefinitionTransitionBuilder
{
    /**
     * Label.
     *
     * @var string
     */
    private $label = null;

    /**
     * From.
     *
     * @var State
     */
    private $from = null;

    /**
     * To.
     *
     * @var State
     */
    private $to = null;

    /**
     * Guard.
     *
     * @var callable
     */
    private $guard = null;

    /**
     * Action.
     *
     * @var callable
     */
    private $action = null;

    /**
     * Definition.
     *
     * @var Definition
     */
    private $definition;

    public function __construct(Definition $definition)
    {
        $this->definition = $definition;
    }

    /**
     * From.
     *
     * @param string|State $state
     * @param string       $type
     *
     * @return $this
     */
    public function from($state, $type = State::TYPE_NORMAL)
    {
        if (!($state instanceof State)) {
            $state = $this->definition->getState($state, $type);
        }

        $this->from = $state;

        return $this;
    }

    /**
     * To.
     *
     * @param string|State $state
     * @param string       $type
     *
     * @return $this
     */
    public function to($state, $type = State::TYPE_NORMAL)
    {
        if (!($state instanceof State)) {
            $state = $this->definition->getState($state, $type);
        }

        $this->to = $state;

        return $this;
    }

    /**
     * When.
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
        if ($this->from === null) {
            throw new \Exception('Missing From State');
        }

        if ($this->to === null) {
            throw new \Exception('Missing To State');
        }

        if ($this->label == null) {
            throw new \Exception('Missing Label');
        }

        $this->from->addTransition(new DefaultTransition($this->label, $this->to, $this->guard, $this->action));

        return $this->definition;
    }
}
