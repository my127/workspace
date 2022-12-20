<?php

namespace my127\FSM\State;

interface StateVisitorClient
{
    /**
     * Accept Visitor.
     *
     * @return void
     */
    public function accept(StateVisitor $visitor);
}
