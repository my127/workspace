<?php

namespace my127\FSM\State;

use my127\FSM\State\State;

interface StateVisitor
{
    /**
     * Visit State
     *
     * @param State $state
     *
     * @return void
     */
    public function visit(State $state);
}
