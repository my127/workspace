<?php

use my127\Workspace\Utility\Filesystem;

class Fixture
{
    private static $WORKSPACE_DIR = '/tmp/my127ws.phpunit';

    private static function clean()
    {
        if (is_dir(self::$WORKSPACE_DIR)) {
            Filesystem::rrmdir(self::$WORKSPACE_DIR);
        }

        mkdir(self::$WORKSPACE_DIR, 0755, true);
    }

    private static function workspaceDir()
    {
        $macPrivateDir = php_uname('s') == 'Darwin' ? '/private' : '';
        self::$WORKSPACE_DIR = $macPrivateDir . sys_get_temp_dir() . '/my127ws.phpunit';
    }

    public static function sampleData($name): string
    {
        self::clean();
        self::workspaceDir();

        if (!is_dir($src = __DIR__.'/samples/'.$name)) {
            throw new Exception("Fixture '{$name}' not found.");
        }

        Filesystem::rcopy($src, self::$WORKSPACE_DIR);
        chdir(self::$WORKSPACE_DIR);
        return self::$WORKSPACE_DIR;
    }

    public static function putWorkspace(string $contents): string
    {
        $path = self::$WORKSPACE_DIR.'/workspace.yml';
        file_put_contents($path, $contents);
        return $path;
    }

    public static function workspace($declarations)
    {
        self::clean();
        self::workspaceDir();
        file_put_contents(self::$WORKSPACE_DIR.'/workspace.yml', $declarations);
        chdir(self::$WORKSPACE_DIR);
        return self::$WORKSPACE_DIR;
    }

    public static function workspaceWithSampleData($declarations, $sample)
    {
        self::sampleData($sample);
        file_put_contents(self::$WORKSPACE_DIR.'/workspace.yml', $declarations);
        chdir(self::$WORKSPACE_DIR);
        return self::$WORKSPACE_DIR;
    }
}
