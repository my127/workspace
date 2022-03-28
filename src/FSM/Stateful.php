<?php

namespace my127\Workspace\FSM;

use my127\Workspace\FSM\State\State;

interface Stateful
{
    /**
     * Get Current State
     *
     * @return State
     */
    public function getCurrentState();

    /**
     * Set Current State
     *
     * @param State $state
     *
     * @return void
     */
    public function setCurrentState(State $state);
}
