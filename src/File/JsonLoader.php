<?php

namespace my127\Workspace\File;

final class JsonLoader
{
    /**
     * @var FileLoader
     */
    private $loader;

    public function __construct(FileLoader $loader)
    {
        $this->loader = $loader;
    }

    public function loadArray(string $url): array
    {
        $contents = $this->loader->load($url);
        return json_decode($contents, true, JSON_THROW_ON_ERROR);
    }
}
