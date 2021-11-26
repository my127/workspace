<?php

namespace my127\Workspace\Types\Workspace;

use my127\Workspace\Path\Path as WorkspacePath;

class Path implements WorkspacePath
{
    public const NAME = 'workspace';

    /** @var Workspace */
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function getRealPath(string $path): string
    {
        return substr_replace($path, $this->workspace->getPath(), 0, 10);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
