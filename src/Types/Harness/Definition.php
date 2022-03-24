<?php

namespace my127\Workspace\Types\Harness;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;

class Definition implements WorkspaceDefinition
{
    public const TYPE = 'harness';

    /** @var string */
    protected $name;

    /** @var string */
    protected $description;

    /** @var array|null */
    protected $require;

    /** @var string */
    protected $path;

    /** @var int */
    protected $scope;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRequiredAttributes(): array
    {
        return $this->require['attributes'] ?? [];
    }

    public function getRequiredConfdPaths()
    {
        return $this->require['confd'] ?? [];
    }

    public function getRequiredServices(): array
    {
        return $this->require['services'] ?? [];
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
