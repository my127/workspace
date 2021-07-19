<?php

namespace my127\Workspace\Updater;

class StdOutput implements Output
{
    public function infof(string $info, ...$args): void
    {
        echo sprintf($info, ...$args) . PHP_EOL;
    }

    public function info(string $string): void
    {
        echo $string . PHP_EOL;
    }
}
