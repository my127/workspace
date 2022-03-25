<?php

namespace my127\Workspace\Console\Usage\Model;

class Argument
{
    private $name;
    private $value;

    public function __construct($name, $value)
    {
        $this->name  = $name;
        $this->value = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return 'argument(\''.$this->name.'\', \''.$this->value.'\')';
    }
}
