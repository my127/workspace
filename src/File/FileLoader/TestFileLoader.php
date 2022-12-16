<?php

namespace my127\Workspace\File\FileLoader;

use my127\Workspace\File\FileLoader;

class TestFileLoader implements FileLoader
{
    /**
     * @var string
     */
    private $contents;

    public function __construct(string $contents)
    {
        $this->contents = $contents;
    }

    public function load(string $url): string
    {
        return $this->contents;
    }
}
