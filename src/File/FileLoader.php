<?php

namespace my127\Workspace\File;

use RuntimeException;
use my127\Workspace\File\Exception\CouldNotLoadFile;
use function file_get_contents;

final class FileLoader
{
    public function load(string $url): string
    {
        $contents = @file_get_contents($url);

        if (false === $contents) {
            throw new CouldNotLoadFile(sprintf(
                'Could not load file at "%s"',
                $url
            ));
        }

        return $contents;
    }
}
