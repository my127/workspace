<?php

namespace my127\FSM\State;

interface StateVisitorClient
{
    /**
     * Accept Visitor
     *
     * @param StateVisitor $visitor
     *
     * @return void
     */
    public function accept(StateVisitor $visitor);
}
