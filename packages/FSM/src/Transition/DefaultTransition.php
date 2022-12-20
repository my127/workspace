<?php

namespace my127\FSM\Transition;

use my127\FSM\Runner\InputSequence;
use my127\FSM\Runner\Runner;
use my127\FSM\State\State;
use my127\FSM\State\StateVisitor;
use my127\FSM\Stateful;

class DefaultTransition implements Transition
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var State
     */
    private $to;

    /**
     * @var callable
     */
    private $guard;

    /**
     * @var callable
     */
    private $action;

    /**
     * Transition.
     *
     * @param string   $label
     * @param State    $to
     * @param callable $guard
     * @param callable $action
     */
    public function __construct($label, $to, $guard = null, $action = null)
    {
        $this->label = $label;
        $this->to = $to;
        $this->guard = $guard;
        $this->action = $action;
    }

    /**
     * Get To.
     *
     * @return State
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set To.
     *
     * @return void
     */
    public function setTo(State $state)
    {
        $this->to = $state;
    }

    /**
     * Accept Visitor.
     *
     * @param State[] $visited
     *
     * @return void
     */
    public function accept(StateVisitor $visitor, &$visited = [])
    {
        $this->to->accept($visitor, $visited);
    }

    /**
     * Can Accept Input.
     *
     * @param mixed $input
     *
     * @return bool
     */
    public function can($input, Stateful $context, Runner $runner)
    {
        if (!is_null($this->guard)) {
            return call_user_func($this->guard, $input, $context, $runner, $this);
        }

        if ($input instanceof InputSequence) {
            if ($input->size() == 0) {
                return false;
            }

            if ($input->peek() == $this->label) {
                return true;
            }
        }

        return $this->label == $input;
    }

    /**
     * Apply Transition.
     *
     * @param mixed $input
     *
     * @return mixed
     */
    public function apply($input, Stateful $context, Runner $runner)
    {
        $context->setCurrentState($this->to);

        if ($this->action !== null && is_callable($this->action)) {
            return call_user_func($this->action, $input, $context, $runner, $this);
        }

        if ($input instanceof InputSequence) {
            $input->pop();
        }

        return $this->action ?: (string) $this->to;
    }

    /**
     * Copy.
     *
     * Perform deep clone
     *
     * @param State[] $visited
     *
     * @return Transition
     */
    public function copy(&$visited = [])
    {
        return new DefaultTransition($this->label, $this->to->copy($visited), $this->guard, $this->action);
    }

    /**
     * Get Label.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->label;
    }
}
