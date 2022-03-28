<?php

namespace my127\Workspace\FSM\State;

use my127\Workspace\FSM\Transition\DefaultTransition;
use my127\Workspace\FSM\State\StateTransitionBuilder;
use my127\Workspace\FSM\State\StateVisitor;
use my127\Workspace\FSM\Transition\Transition;
use my127\Workspace\FSM\State\DefaultState;

class DefaultState implements State
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $type;

    /**
     * Transitions
     *
     * @var Transition[]
     */
    private $transitions = [];

    /**
     * State
     *
     * @param string $label
     * @param string $type
     */
    public function __construct($label, $type = self::TYPE_NORMAL)
    {
        $this->label = $label;
        $this->type  = $type;
    }

    /**
     * Add Transition
     *
     * Usage :-
     *
     * addTransition(Transition)
     * addTransition(Label, To [,Guard [,Action]])
     *
     * @param string|Transition $transition
     * @param State             $to
     * @param callable          $guard
     * @param callable          $action
     *
     * @return void
     */
    public function addTransition($transition, State $to = null, $guard = null, $action = null)
    {
        if (!($transition instanceof Transition)) {
            $transition = new DefaultTransition($transition, $to, $guard, $action);
        }

        $this->transitions[] = $transition;
    }

    /**
     * Set Type
     *
     * @param string $type
     *
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Is Initial
     *
     * @return bool
     */
    public function isInitial()
    {
        return $this->type == self::TYPE_INITIAL;
    }

    /**
     * Is Normal
     *
     * @return bool
     */
    public function isNormal()
    {
        return $this->type == self::TYPE_NORMAL;
    }

    /**
     * Is Terminal
     *
     * @return bool
     */
    public function isTerminal()
    {
        return $this->type == self::TYPE_TERMINAL;
    }

    /**
     * Get Transitions
     *
     * @return Transition[]
     */
    public function getTransitions()
    {
        return $this->transitions;
    }

    /**
     * Create Transition
     *
     * Fluid counterpart to addTransition
     *
     * @param string   $label
     * @param callable $guard
     *
     * @return StateTransitionBuilder
     */
    public function when($label, callable $guard = null)
    {
        return (new StateTransitionBuilder($this))->when($label, $guard);
    }

    /**
     * Copy State
     *
     * Perform a deep clone
     *
     * @param State[] $visited
     *
     * @return State
     */
    public function copy(&$visited = [])
    {
        $hash = spl_object_hash($this);

        if (isset($visited[$hash])) {
            return $visited[$hash];
        }

        $copy = $visited[$hash] = new DefaultState($this->label, $this->type);

        foreach ($this->transitions as $transition) {
            $copy->addTransition($transition->copy($visited));
        }

        return $copy;
    }

    /**
     * Accept Visitor
     *
     * @param StateVisitor $visitor
     * @param State[]      $visited
     *
     * @return void
     */
    public function accept(StateVisitor $visitor, &$visited = [])
    {
        if (!isset($visited[$hash = spl_object_hash($this)])) {
            $visitor->visit($visited[$hash] = $this);

            foreach ($this->transitions as $transition) {
                $transition->accept($visitor, $visited);
            }
        }
    }

    /**
     * Get Label
     *
     * @return string
     */
    public function __toString()
    {
        return $this->label;
    }
}
