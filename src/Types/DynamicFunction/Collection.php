<?php

namespace my127\Workspace\Types\DynamicFunction;

class Collection implements \IteratorAggregate
{
    /** @var array<string,DynamicFunction> */
    private $functions = [];

    public function add(DynamicFunction $function)
    {
        $this->functions[$function->getName()] = $function;
    }

    public function get(string $function): DynamicFunction
    {
        return $this->functions[$function];
    }

    public function call($name, ...$arguments)
    {
        call_user_func_array($this->functions[$name], $arguments);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->functions);
    }
}
