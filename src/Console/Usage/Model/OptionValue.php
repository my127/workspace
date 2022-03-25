<?php

namespace my127\Workspace\Console\Usage\Model;

use my127\Workspace\Console\Usage\Model\OptionValue;

interface OptionValue
{
    public function equals(OptionValue $value): bool;
    public function value();
}
