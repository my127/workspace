<?php

namespace my127\Workspace\Types\DynamicFunction;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;

class Definition implements WorkspaceDefinition
{
    const TYPE = 'function';

    /** @var string */
    private $name;

    /** @var string[] */
    private $env;

    /** @var string */
    private $exec;

    /** @var string[] */
    private $arguments;

    /** @var string */
    private $path;

    /** @var int */
    private $scope;

    public function getName(): string
    {
        return $this->name;
    }

    public function getEnvironmentVariables(): array
    {
        return $this->env;
    }

    public function getExec(): string
    {
        return $this->exec;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getScope(): int
    {
        return $this->scope;
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
