<?php

namespace my127\Console\Usage\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class OptionDefinitionCollection implements IteratorAggregate, Countable
{
    private $options = [];
    private $map = [];

    public function add(OptionDefinition $optionDefinition)
    {
        $this->options[$optionDefinition->getLabel()] = $optionDefinition;

        if (($shortName = $optionDefinition->getShortName()) !== null) {
            $this->map[$shortName] = $optionDefinition;
        }

        if (($longName = $optionDefinition->getLongName()) !== null) {
            $this->map[$longName] = $optionDefinition;
        }
    }

    public function find($optionName)
    {
        return (isset($this->map[$optionName])) ? $this->map[$optionName] : null;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->options);
    }

    public function merge(OptionDefinitionCollection $toMerge)
    {
        $merged = clone $this;

        foreach ($toMerge->options as $option) {
            $merged->add($option);
        }

        return $merged;
    }

    public function count()
    {
        return count($this->options);
    }
}
