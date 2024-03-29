<?php

namespace my127\Workspace\Types\Workspace;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;

class Definition implements WorkspaceDefinition
{
    public const TYPE = 'workspace';

    /** @var string */
    protected $name;

    /** @var ?string */
    protected $description = null;

    /** @var ?string[] */
    protected $harnessLayers = [];

    /** @var string */
    protected $path;

    /** @var ?string */
    protected $overlay = null;

    /** @var array|null */
    protected $require;

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

    /**
     * @return string[]
     */
    public function getHarnessLayers(): ?array
    {
        return $this->harnessLayers;
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

    public function getRequiredWorkspaceVersion(): ?string
    {
        return $this->require['workspace'] ?? null;
    }
}
