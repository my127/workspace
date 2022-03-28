<?php

namespace my127\Workspace\Console\Application\Action;

use my127\Workspace\Console\Usage\Input;

interface Action
{
    public function __invoke(Input $input);
    public static function getName(): string;
}