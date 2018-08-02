<?php

namespace my127\Workspace\Types\Attribute;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;

class Definition implements WorkspaceDefinition
{
    public const PRIORITY_DEFAULT  = 1;
    public const PRIORITY_NORMAL   = 2;
    public const PRIORITY_OVERRIDE = 3;

    /** @var string */
    private $key;

    /** @var mixed */
    private $value;

    /** @var string */
    private $path;

    /** @var int */
    private $scope;

    /** @var string */
    private $type;

    /** @var int */
    private $priority;

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
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
        return $this->type;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
