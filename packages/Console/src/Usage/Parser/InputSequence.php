<?php

namespace my127\Console\Usage\Parser;

use Countable;
use my127\Console\Usage\Model\OptionDefinition;

class InputSequence implements Countable
{
    private $options    = [];
    private $positional = [];

    public function __construct($options, $positional)
    {
        $this->options    = $options;
        $this->positional = $positional;
    }

    public function peek()
    {
        return end($this->positional);
    }

    public function pop()
    {
        return array_pop($this->positional);
    }

    public function getOption(OptionDefinition $definition)
    {
        $key = $definition->getLabel();

        if (!isset($this->options[$key])) {
            return null;
        }

        $option = array_pop($this->options[$key]);

        if (empty($this->options[$key])) {
            unset($this->options[$key]);
        }

        return $option;
    }

    public function hasPositional()
    {
        return !empty($this->positional);
    }

    public function hasOption(OptionDefinition $definition)
    {
        return isset($this->options[$definition->getLabel()]);
    }

    public function count(): int
    {
        return count($this->positional) + count($this->options);
    }
}
