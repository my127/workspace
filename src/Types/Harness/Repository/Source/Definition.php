<?php

namespace my127\Workspace\Types\Harness\Repository\Source;

use my127\Workspace\Definition\Definition as WorkspaceDefinition;

class Definition implements WorkspaceDefinition
{
    const TYPE = 'harness.repository.source';

    private $path;
    private $scope;
    private $name;
    private $url;

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
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
