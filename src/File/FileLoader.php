<?php

namespace my127\Workspace\File;

interface FileLoader
{
    public function load(string $url): string;
}
