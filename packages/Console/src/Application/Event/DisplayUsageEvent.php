<?php

namespace my127\Console\Application\Event;

use my127\Console\Usage\Model\OptionDefinitionCollection;
use my127\Console\Usage\Parser\InputSequence;
use my127\Console\Usage\Parser\InputSequenceFactory;
use Symfony\Contracts\EventDispatcher\Event;

class DisplayUsageEvent extends Event
{
    private InputSequence $input;

    public function __construct($args, private OptionDefinitionCollection $options, private bool $validCommand = true)
    {
        $this->input = (new InputSequenceFactory())->createFrom($args, $options, true);
    }

    public function getInputSequence(): InputSequence
    {
        return clone $this->input;
    }

    public function validCommand(): bool
    {
        return $this->validCommand;
    }
}
