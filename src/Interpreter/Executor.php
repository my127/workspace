<?php

namespace my127\Workspace\Interpreter;

interface Executor
{
    public function exec(string $script, array $args = [], string $cwd = null, array $env = []): void;
    public function capture(string $script, array $args = [], string $cwd = null, array $env = []);
    public function getName(): string;
}
