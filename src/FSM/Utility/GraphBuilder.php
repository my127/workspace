<?php

namespace my127\Workspace\FSM\Utility;

use my127\Workspace\FSM\State\State;
use my127\Workspace\FSM\State\StateVisitor;
use my127\Workspace\FSM\State\StateVisitorClient;
use SplObjectStorage;

class GraphBuilder implements StateVisitor
{
    /**
     * @var SplObjectStorage
     */
    public $transitions;

    private $id = 0;

    public function __construct(StateVisitorClient $state)
    {
        $this->transitions = new SplObjectStorage();
        $state->accept($this);
    }

    /**
     * Visit State
     *
     * @param State $state
     *
     * @return void
     */
    public function visit(State $state)
    {
        foreach ($state->getTransitions() as $transition) {
            $this->transitions[$transition] = 'T'.(++$this->id);
        }
    }
}
