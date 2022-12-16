<?php

namespace my127\Console\Usage\Model;

interface OptionValue
{
    public function equals(OptionValue $value): bool;
    public function value();
}
