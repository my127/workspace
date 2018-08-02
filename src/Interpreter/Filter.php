<?php

namespace my127\Workspace\Interpreter;

interface Filter
{
    public function getName(): string;
    public function apply(string $script): string;
}
