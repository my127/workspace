<?php

namespace my127\Workspace\FSM\Runner;

use my127\Workspace\FSM\State\State;
use my127\Workspace\FSM\State\State;

interface Runner
{
    /**
     * Input
     *
     * @param $input
     *
     * @return mixed
     */
    public function input($input);

    /**
     * @return callable
     */
    public function getLambda();

    /**
     * Alias of input
     *
     * @param $input
     *
     * @return mixed
     */
    public function __invoke($input);

    /**
     * Get Current State
     *
     * @return State
     */
    public function getCurrentState();
}
