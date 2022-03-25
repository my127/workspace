<?php

namespace my127\Workspace\Console\Usage\Model;

class Option
{
    private $name;
    private $definition;
    private $value;

    public function __construct($name, OptionDefinition $definition, $value = null)
    {
        $this->name       = $name;
        $this->definition = $definition;
        $this->value      = $value;
    }

    public function getDefinition()
    {
        return $this->definition;
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
        $value = $this->value;

        if (is_bool($value)) {
            $value = ($value) ? 'true' : 'false';
        }

        return 'option(\''.$this->name.'\', \''.$value.'\')';
    }
}
