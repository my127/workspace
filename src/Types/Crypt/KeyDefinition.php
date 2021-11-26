<?php

namespace my127\Workspace\Types\Crypt;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;

class KeyDefinition implements WorkspaceDefinition
{
    public const TYPE = 'key';

    /** @var string */
    private $name;

    /** @var string */
    private $key;

    /** @var string */
    private $path;

    /** @var int */
    private $scope;

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getScope(): int
    {
        return $this->scope;
    }
}
