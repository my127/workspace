<?php

namespace my127\Console\Usage\Parser;

use my127\Console\Usage\Model\Option;
use my127\Console\Usage\Model\OptionDefinition;

class InputSequence implements \Countable
{
    /**
     * @param array<string, list<OptionDefinition>> $options
     * @param array<string, string> $positional
     */
    public function __construct(private $options, private $positional)
    {
    }

    public function peek(): ?string
    {
        return end($this->positional) ?: null;
    }

    public function pop(): ?string
    {
        return array_pop($this->positional) ?: null;
    }

    public function getOption(OptionDefinition $definition): ?Option
    {
        $key = $definition->getLabel();

        if (!isset($this->options[$key])) {
            return null;
        }

        $option = array_pop($this->options[$key]);

        if (empty($this->options[$key])) {
            unset($this->options[$key]);
        }

        return $option;
    }

    public function hasPositional(): bool
    {
        return !empty($this->positional);
    }

    public function hasOption(OptionDefinition $definition): bool
    {
        return isset($this->options[$definition->getLabel()]);
    }

    public function count(): int
    {
        return count($this->positional) + count($this->options);
    }

    public function toArgumentString(): string
    {
        $args = array_reverse($this->positional);
        array_shift($args);
        return implode(' ', $args);
    }
}
