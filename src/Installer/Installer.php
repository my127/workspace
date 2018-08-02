<?php

namespace my127\Workspace\Installer;

use my127\Workspace\Utility\Filesystem;

class Installer
{
    public static function install(string $home)
    {
        $path = $home.'/.my127/workspace';

        if (is_dir($path)) {
            Filesystem::rrmdir($path);
        }

        mkdir($path, 0755, true);

        Filesystem::rcopy(__DIR__.'/../../home', $path);

        passthru('chmod +x '.$path.'/bin/*');
        passthru('chmod +x '.$path.'/lib/*.sh');

        touch($path.'/.installed');
    }
}
