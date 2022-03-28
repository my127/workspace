<?php

namespace my127\Workspace\FSM\State;

use my127\Workspace\FSM\State\State;

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
