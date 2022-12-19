<?php

namespace my127\FSM\State;

interface StateVisitor
{
    /**
     * Visit State.
     *
     * @return void
     */
    public function visit(State $state);
}
