<?php

namespace my127\Console\Application\Action;

use my127\Console\Usage\Input;

interface Action
{
    public function __invoke(Input $input);
    public static function getName(): string;
}