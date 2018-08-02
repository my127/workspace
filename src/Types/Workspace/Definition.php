<?php

namespace my127\Workspace\Types\Workspace;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;

class Definition implements WorkspaceDefinition
{
    const TYPE = 'workspace';

    /** @var string */
    protected $name;

    /** @var ?string */
    protected $description = null;

    /** @var ?string */
    protected $harnessName = null;

    /** @var string */
    protected $path;

    /** @var string */
    protected $overlay = null;

    /** @var int */
    protected $scope;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getHarnessName(): ?string
    {
        return $this->harnessName;
    }

    public function getOverlayPath(): ?string
    {
        return $this->overlay;
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
