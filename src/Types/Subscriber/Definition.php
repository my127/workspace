<?php

namespace my127\Workspace\Types\Subscriber;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;

class Definition implements WorkspaceDefinition
{
    /** @var string */
    private $type;

    /** @var string[] */
    private $env;

    /** @var string */
    private $exec;

    /** @var string */
    private $path;

    /** @var string */
    private $event;

    /** @var int */
    private $scope;

    public function getEnvironmentVariables(): array
    {
        return $this->env;
    }

    public function getExec(): string
    {
        return $this->exec;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getScope(): int
    {
        return $this->scope;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
