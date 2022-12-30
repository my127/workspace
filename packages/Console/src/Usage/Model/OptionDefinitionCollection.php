<?php

namespace my127\Console\Usage\Model;

use ArrayIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<string,OptionDefinition>
 */
class OptionDefinitionCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var array<string, OptionDefinition>
     */
    private $options = [];

    /**
     * @var array<string, OptionDefinition>
     */
    private $map = [];

    public function add(OptionDefinition $optionDefinition): void
    {
        $this->options[$optionDefinition->getLabel()] = $optionDefinition;

        if (($shortName = $optionDefinition->getShortName()) !== null) {
            $this->map[$shortName] = $optionDefinition;
        }

        if (($longName = $optionDefinition->getLongName()) !== null) {
            $this->map[$longName] = $optionDefinition;
        }
    }

    public function find($optionName): ?OptionDefinition
    {
        return (isset($this->map[$optionName])) ? $this->map[$optionName] : null;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->options);
    }

    public function merge(OptionDefinitionCollection $toMerge): OptionDefinitionCollection
    {
        $merged = clone $this;

        foreach ($toMerge->options as $option) {
            $merged->add($option);
        }

        return $merged;
    }

    public function count(): int
    {
        return count($this->options);
    }
}
