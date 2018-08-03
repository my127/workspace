<?php

use my127\Workspace\Utility\Filesystem;

class Fixture
{
    public static function workspace($name)
    {
        if (!is_dir($src = __DIR__.'/samples/'.$name)) {
            throw new Exception("Fixture '{$name}' not found.");
        }

        if (is_dir($dst = '/tmp/my127ws.phpunit')) {
            Filesystem::rrmdir($dst);
        }

        mkdir($dst, 0755, true);
        Filesystem::rcopy($src, $dst);
        chdir($dst);
    }
}
