<?php

namespace my127\FSM;

use my127\FSM\State\State;

interface Stateful
{
    /**
     * Get Current State.
     *
     * @return State
     */
    public function getCurrentState();

    /**
     * Set Current State.
     *
     * @return void
     */
    public function setCurrentState(State $state);
}
