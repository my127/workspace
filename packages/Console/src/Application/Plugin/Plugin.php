<?php

namespace my127\Console\Application\Plugin;

use my127\Console\Application\Application;

interface Plugin
{
    public function setup(Application $application): void;
}
