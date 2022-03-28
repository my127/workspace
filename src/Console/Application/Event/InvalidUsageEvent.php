<?php

namespace my127\Workspace\Console\Application\Event;

use my127\Workspace\Console\Usage\Model\OptionDefinitionCollection;
use my127\Workspace\Console\Usage\Parser\InputSequence;
use my127\Workspace\Console\Usage\Parser\InputSequenceFactory;
use Symfony\Component\EventDispatcher\Event;

class InvalidUsageEvent extends Event
{
    private $input;

    public function __construct($args, OptionDefinitionCollection $options)
    {
        $this->input = (new InputSequenceFactory())->createFrom($args, $options);
    }

    public function getInputSequence(): InputSequence
    {
        return clone $this->input;
    }
}
