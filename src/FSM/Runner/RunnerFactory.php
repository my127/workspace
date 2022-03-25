<?php

namespace my127\Workspace\FSM\Runner;

use my127\Workspace\FSM\Runner\Runner;
use my127\Workspace\FSM\Runner\StepRunner;
use my127\Workspace\FSM\State\State;
use my127\Workspace\FSM\Stateful;
use my127\Workspace\FSM\Runner\Runner;

class RunnerFactory
{
    private $defaultRunner = StepRunner::class;

    /**
     * Build FSM
     *
     * @param State    $initialState
     * @param Stateful $context
     *
     * @return Runner
     */
    public function buildFSM(State $initialState, Stateful $context = null)
    {
        return new $this->defaultRunner($initialState, $context);
    }
}
