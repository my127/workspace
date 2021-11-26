<?php

namespace my127\Workspace\Types\Confd;

class Collection
{
    /** @var Definition[] */
    private $definitions = [];

    public function add(Definition $definition): void
    {
        $this->definitions[$definition->getDirectory()] = $definition;
    }

    public function get(string $directory): Definition
    {
        return $this->definitions[$directory];
    }
}
