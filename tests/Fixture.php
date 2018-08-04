<?php

use my127\Workspace\Utility\Filesystem;

class Fixture
{
    private const WORKSPACE_DIR = '/tmp/my127ws.phpuint';

    private static function clean()
    {
        if (is_dir(self::WORKSPACE_DIR)) {
            Filesystem::rrmdir(self::WORKSPACE_DIR);
        }

        mkdir(self::WORKSPACE_DIR, 0755, true);
    }

    public static function sampleData($name): string
    {
        self::clean();

        if (!is_dir($src = __DIR__.'/samples/'.$name)) {
            throw new Exception("Fixture '{$name}' not found.");
        }

        Filesystem::rcopy($src, self::WORKSPACE_DIR);
        chdir(self::WORKSPACE_DIR);
        return self::WORKSPACE_DIR;
    }

    public static function workspace($declarations)
    {
        self::clean();
        file_put_contents(self::WORKSPACE_DIR.'/workspace.yml', $declarations);
        chdir(self::WORKSPACE_DIR);
        return self::WORKSPACE_DIR;
    }

    public static function workspaceWithSampleData($declarations, $sample)
    {
        self::sampleData($sample);
        file_put_contents(self::WORKSPACE_DIR.'/workspace.yml', $declarations);
        chdir(self::WORKSPACE_DIR);
        return self::WORKSPACE_DIR;
    }
}
