<?php

namespace my127\Workspace\Updater;

interface Output
{
    public function infof(string $info, ...$args): void;

    public function info(string $string): void;
}
