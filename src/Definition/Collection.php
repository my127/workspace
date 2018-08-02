<?php

namespace my127\Workspace\Definition;

class Collection
{
    /** @var Definition[][]  */
    private $definitions = [];

    public function add(Definition $definition)
    {
        $this->definitions[$definition->getType()][] = $definition;
    }

    public function findByType(string $type): array
    {
        return $this->definitions[$type]??[];
    }

    public function findOneByType(string $type): ?Definition
    {
        return $this->definitions[$type][0]??null;
    }
}
