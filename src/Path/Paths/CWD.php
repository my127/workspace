<?php

namespace my127\Workspace\Path\Paths;

use my127\Workspace\Path\Path;

class CWD implements Path
{
    const NAME = 'cwd';

    public function getRealPath(string $path): string
    {
        return substr_replace($path, getcwd(), 0, 4);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
