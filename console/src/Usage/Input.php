<?php

namespace my127\Console\Usage;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use my127\Console\Usage\Model\Argument;
use my127\Console\Usage\Model\Command;
use my127\Console\Usage\Model\OptionDefinition;
use my127\Console\Usage\Model\OptionDefinitionCollection;
use my127\Console\Usage\Model\Option;

class Input implements ArrayAccess, Countable, IteratorAggregate
{
    private $arguments = [];
    private $options   = [];
    private $command   = [];
    private $args      = [];

    /**
     * @param mixed[]                     $args
     * @param OptionDefinitionCollection  $optionRepository
     */
    public function __construct($args, OptionDefinitionCollection $optionRepository)
    {
        $this->args = $args;

        $this->processArgs($optionRepository);
    }

    public function command($offset = 0, $length = null)
    {
        return implode(' ', array_slice($this->command, $offset, $length));
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function argument($argument)
    {
        return $this->getArgument($argument);
    }

    public function getArgument($argument)
    {
        if (!isset($this->arguments[$argument])) {
            return null;
        }

        $values = $this->arguments[$argument];
        return (count($values) == 1) ? $values[0] : $values;
    }

    public function option($option)
    {
        return $this->getOption($option);
    }

    public function getOption($option)
    {
        if (!isset($this->options[$option])) {
            return null;
        }

        $values = $this->options[$option];
        return (count($values) == 1) ? $values[0] : $values;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return $this->args[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->args[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->args[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->args[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->args);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->args);
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return implode("\n", array_map(function ($arg) {
            return (string) $arg;

        }, $this->args));
    }

    public function toJSON()
    {
        $data =
        [
            'argv'      => array_map(function ($arg) {
                return (string) $arg;
            }, $this->args),
            'command'   => $this->command,
            'arguments' => array_map(function ($values) {
                return count($values) == 1 ? $values[0] : $values;
            }, $this->arguments),
            'options'   => array_map(function ($values) {
                return count($values) == 1 ? $values[0] : $values;
            }, $this->options)
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    private function processArgs(OptionDefinitionCollection $optionRepository)
    {
        /** @var OptionDefinition $optionDefinition */
        foreach ($optionRepository as $optionDefinition) {
            $this->options[$optionDefinition->getLongName() ?: $optionDefinition->getShortName()] = null;
        }

        foreach ($this->args as $arg) {
            switch (true) {
                case ($arg instanceof Command):
                    $this->command[] = $arg->getName();
                    break;

                case ($arg instanceof Argument):
                    $this->arguments[$arg->getName()][] = $arg->getValue();
                    break;

                case ($arg instanceof Option):
                    $this->options[$this->getOptionName($arg)][] = $arg->getValue();
                    break;
            }
        }

        foreach ($this->options as $key => $value) {
            if ($value === null) {
                $this->options[$key] = [$optionRepository->find($key)->getDefault()];
            }
        }
    }

    private function getOptionName(Option $option)
    {
        $definition = $option->getDefinition();

        return $definition->getLongName()?:$definition->getShortName();
    }
}
