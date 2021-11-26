<?php

namespace my127\Workspace\Types\Harness\Repository\Package;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;

class Definition implements WorkspaceDefinition
{
    public const TYPE = 'harness.repository.package';

    private $path;
    private $scope;
    private $name;
    private $version;
    private $dist;

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getDist(): array
    {
        return $this->dist;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getScope(): int
    {
        return $this->scope;
    }
}
