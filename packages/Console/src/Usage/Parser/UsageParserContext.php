<?php

namespace my127\Console\Usage\Parser;

use my127\FSM\State\State;
use my127\FSM\Stateful;
use my127\FSM\Transition\Transition;
use SplObjectStorage;

class UsageParserContext implements Stateful
{
    private $currentState = null;

    /**
     * @var SplObjectStorage
     */
    private $data;

    public function __construct()
    {
        $this->data = new SplObjectStorage();
    }

    /**
     * Get Current State
     *
     * @return State
     */
    public function getCurrentState()
    {
        return $this->currentState;
    }

    /**
     * Set Current State
     *
     * @param State $state
     *
     * @return void
     */
    public function setCurrentState(State $state)
    {
        $this->currentState = $state;
    }

    /**
     * @param Transition $transition
     *
     * @return mixed
     */
    public function getData(Transition $transition)
    {
        return isset($this->data[$transition]) ? $this->data[$transition] : null;
    }

    /**
     * @param Transition $transition
     * @param mixed      $data
     */
    public function setData(Transition $transition, $data)
    {
        $this->data[$transition] = $data;
    }

    public function __clone()
    {
        $data = $this->data;

        $this->data = new SplObjectStorage();
        $this->data->addAll($data);
    }
}
