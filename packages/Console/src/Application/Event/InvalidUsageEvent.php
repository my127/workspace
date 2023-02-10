<?php

namespace my127\Console\Application\Event;

use my127\Console\Usage\Model\OptionDefinitionCollection;
use my127\Console\Usage\Parser\InputSequence;
use my127\Console\Usage\Parser\InputSequenceFactory;
use Symfony\Contracts\EventDispatcher\Event;

class InvalidUsageEvent extends Event
{
    private InputSequence $input;

    public function __construct($args, private OptionDefinitionCollection $options)
    {
        $this->input = (new InputSequenceFactory())->createFrom($args, $options, true);
    }

    public function getInputSequence(): InputSequence
    {
        return clone $this->input;
    }

    public function getOptions(): OptionDefinitionCollection
    {
        return $this->options;
    }
}
