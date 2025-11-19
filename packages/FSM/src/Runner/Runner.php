<?php

namespace my127\FSM\Runner;

use my127\FSM\State\State;

interface Runner
{
    /**
     * Input.
     */
    public function input($input);

    /**
     * @return callable
     */
    public function getLambda();

    /**
     * Alias of input.
     */
    public function __invoke($input);

    /**
     * Get Current State.
     *
     * @return State
     */
    public function getCurrentState();
}
