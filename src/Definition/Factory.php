<?php

namespace my127\Workspace\Definition;

interface Factory
{
    public function create(array $data): Definition;
    public static function getTypes(): array;
}
