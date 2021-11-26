<?php

namespace my127\Workspace\Path;

class Composite implements Path
{
    public const NAME = 'composite';

    /** @var Path[] */
    private $paths = [];

    public function add(Path $path): void
    {
        $this->paths[$path->getName()] = $path;
    }

    public function getRealPath($path): string
    {
        return $this->paths[strtok($path, ':')]->getRealPath($path);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
