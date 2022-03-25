<?php

namespace my127\Workspace\FSM\State;

use my127\Workspace\FSM\State\StateVisitor;

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
