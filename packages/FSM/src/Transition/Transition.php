<?php

namespace my127\FSM\Transition;

use my127\FSM\Runner\Runner;
use my127\FSM\State\State;
use my127\FSM\State\StateVisitor;
use my127\FSM\State\StateVisitorClient;
use my127\FSM\Stateful;

interface Transition extends StateVisitorClient
{
    /**
     * Get To.
     *
     * @return State
     */
    public function getTo();

    /**
     * Set To.
     *
     * @return void
     */
    public function setTo(State $state);

    /**
     * Accept Visitor.
     *
     * @param State[] $visited
     *
     * @return void
     */
    public function accept(StateVisitor $visitor, &$visited = []);

    /**
     * Can Accept Input.
     *
     * @param mixed $input
     *
     * @return bool
     */
    public function can($input, Stateful $context, Runner $runner);

    /**
     * Apply Transition.
     *
     * @param mixed $input
     *
     * @return mixed
     */
    public function apply($input, Stateful $context, Runner $runner);

    /**
     * Copy.
     *
     * Perform deep clone
     *
     * @param State[] $visited
     *
     * @return Transition
     */
    public function copy(&$visited = []);

    /**
     * Get Label.
     *
     * @return string
     */
    public function __toString();
}
