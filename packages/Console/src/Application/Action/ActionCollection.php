<?php

namespace my127\Console\Application\Action;

class ActionCollection
{
    private $actions = [];

    public function add(callable $action, string $name = ''): void
    {
        $this->actions[($action instanceof Action)?$action::getName():$name] = $action;
    }

    public function get(string $name): callable
    {
        return $this->actions[$name];
    }
}
