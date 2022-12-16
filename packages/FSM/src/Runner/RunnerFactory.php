<?php

namespace my127\FSM\Runner;

use my127\FSM\Runner\Runner;
use my127\FSM\Runner\StepRunner;
use my127\FSM\State\State;
use my127\FSM\Stateful;

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
