<?php

namespace my127\Workspace\Path;

interface Path
{
    public function getName(): string;

    public function getRealPath(string $path): string;
}
