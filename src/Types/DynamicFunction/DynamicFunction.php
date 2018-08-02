<?php

namespace my127\Workspace\Types\DynamicFunction;

use my127\Workspace\Interpreter\Interpreter;

class DynamicFunction
{
    /** @var Interpreter */
    private $interpreter;

    /** @var Definition */
    private $definition;

    public function __construct(Interpreter $interpreter, Definition $definition)
    {
        $this->interpreter = $interpreter;
        $this->definition  = $definition;
    }

    public function getName()
    {
        return $this->definition->getName();
    }

    public function __invoke()
    {
        $exec = $this->definition->getExec();
        $env  = $this->definition->getEnvironmentVariables();
        $args = $this->definition->getArguments();

        return $this->interpreter->script($exec, $args)->capture(func_get_args(), $env);
    }
}
