<?php

namespace my127\Workspace\Updater;

class Output
{
    public function infof(string $info, ...$args): void
    {
        echo sprintf($info, ...$args) . PHP_EOL;
    }

    public function info(string $string)
    {
        echo $string . PHP_EOL;
    }
}
