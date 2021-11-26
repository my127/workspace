<?php

namespace my127\Workspace\Definition;

interface Definition
{
    public const SCOPE_GLOBAL = 1;
    public const SCOPE_WORKSPACE = 2;
    public const SCOPE_HARNESS = 3;

    public function getType(): string;

    public function getScope(): int;
}
