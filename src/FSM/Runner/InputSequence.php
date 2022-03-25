<?php

namespace my127\Workspace\FSM\Runner;

use Countable;

class InputSequence implements Countable
{
    private $input;

    public function __construct($input)
    {
        $this->input = array_reverse($input);
    }

    public function peek()
    {
        return end($this->input);
    }

    public function pop()
    {
        return array_pop($this->input);
    }

    public function push($symbol)
    {
        $this->input[] = $symbol;
    }

    public function size()
    {
        return count($this->input);
    }

    public function count()
    {
        return count($this->input);
    }
}
