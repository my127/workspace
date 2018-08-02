<?php

namespace my127\Workspace\Types\Harness;

use my127\Workspace\Path\Path as WorkspacePath;

class Path implements WorkspacePath
{
    const NAME = 'harness';

    private $harness;

    public function __construct(Harness $harness)
    {
        $this->harness = $harness;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getRealPath(string $path): string
    {
        return substr_replace($path, $this->harness->getPath(), 0, 8);
    }
}
