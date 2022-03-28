<?php

namespace my127\Workspace\Console\Usage\Model;

class Command
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return 'command(\''.$this->name.'\')';
    }
}
