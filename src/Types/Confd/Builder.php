<?php

namespace my127\Workspace\Types\Confd;

use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Environment\Builder as EnvironmentBuilder;

class Builder implements EnvironmentBuilder
{
    /** @var Collection */
    private $collection;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function build(DefinitionCollection $definitions)
    {
        foreach ($definitions->findByType(Definition::TYPE) as $definition) {
            $this->collection->add($definition);
        }
    }
}
