<?php

namespace my127\Workspace\Tests\Test\Updater;

use my127\Workspace\Updater\Output;

class NullOutput implements Output
{
    public function infof(string $info, ...$args): void
    {
    }

    public function info(string $info): void
    {
    }

    public function success(string $success): void
    {
    }
}
