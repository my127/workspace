<?php

namespace my127\FSM\Runner;

class InputSequence implements \Countable
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

    public function count(): int
    {
        return count($this->input);
    }
}
