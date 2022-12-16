<?php

namespace my127\Workspace\File\FileLoader;

use function file_get_contents;
use my127\Workspace\File\Exception\CouldNotLoadFile;
use my127\Workspace\File\FileLoader;

final class FileGetContentsLoader implements FileLoader
{
    public function load(string $url): string
    {
        $contents = @file_get_contents($url);

        if ($contents === false) {
            throw new CouldNotLoadFile(sprintf('Could not load file at "%s"', $url));
        }

        return $contents;
    }
}
