<?php

namespace my127\Workspace\Environment;

class BuilderCollection implements \IteratorAggregate
{
    /** @var Builder[] */
    private $builders = [];

    public function add(Builder $builder): void
    {
        $this->builders[] = $builder;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->builders);
    }
}
