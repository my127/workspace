<?php

namespace my127\Workspace\Types\Harness\Repository;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;

class Definition implements WorkspaceDefinition
{
    public const TYPE = 'harness.repository';

    /** @var string */
    private $path;

    /** @var string */
    private $name;

    /** @var array */
    private $packages;

    /** @var int */
    private $scope;

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPackages(): array
    {
        return $this->packages;
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
