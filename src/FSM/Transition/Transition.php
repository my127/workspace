<?php

namespace my127\Workspace\FSM\Transition;

use my127\Workspace\FSM\Runner\Runner;
use my127\Workspace\FSM\State\State;
use my127\Workspace\FSM\State\StateVisitor;
use my127\Workspace\FSM\State\StateVisitorClient;
use my127\Workspace\FSM\Stateful;

interface Transition extends StateVisitorClient
{
    /**
     * Get To
     *
     * @return State
     */
    public function getTo();

    /**
     * Set To
     *
     * @param State $state
     *
     * @return void
     */
    public function setTo(State $state);

    /**
     * Accept Visitor
     *
     * @param StateVisitor $visitor
     * @param State[]      $visited
     *
     * @return void
     */
    public function accept(StateVisitor $visitor, &$visited = []);

    /**
     * Can Accept Input
     *
     * @param mixed    $input
     * @param Stateful $context
     * @param Runner   $runner
     *
     * @return bool
     */
    public function can($input, Stateful $context, Runner $runner);

    /**
     * Apply Transition
     *
     * @param mixed    $input
     * @param Stateful $context
     * @param Runner   $runner
     *
     * @return mixed
     */
    public function apply($input, Stateful $context, Runner $runner);

    /**
     * Copy
     *
     * Perform deep clone
     *
     * @param State[] $visited
     *
     * @return Transition
     */
    public function copy(&$visited = []);

    /**
     * Get Label
     *
     * @return string
     */
    public function __toString();
}
