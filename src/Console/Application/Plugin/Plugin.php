<?php

namespace my127\Workspace\Console\Application\Plugin;

use my127\Workspace\Console\Application\Application;

interface Plugin
{
    public function setup(Application $application): void;
}