<?php

namespace my127\Workspace\Types\Confd;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;

class Definition implements WorkspaceDefinition
{
    public const TYPE = 'confd';

    /** @var string */
    private $path;

    /** @var string */
    private $directory;

    /** @var array */
    private $templates;

    /** @var int */
    private $scope;

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getTemplates(): array
    {
        return $this->templates;
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
