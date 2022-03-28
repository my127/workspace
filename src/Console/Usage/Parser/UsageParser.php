<?php

namespace my127\Workspace\Console\Usage\Parser;

use my127\Workspace\Console\Usage\Input;
use my127\Workspace\Console\Usage\Model\OptionDefinitionCollection;
use my127\Workspace\FSM\Runner\BacktrackingRunner;
use my127\Workspace\FSM\Definition;
use my127\Workspace\Console\Usage\Parser\InputSequence;
use my127\Workspace\Console\Usage\Parser\InputSequenceFactory;

class UsageParser
{
    /**
     * @var Definition
     */
    private $usageDefinition;

    /**
     * @var OptionDefinitionCollection
     */
    private $optionRepository;

    /**
     * @param Definition $usageDefinition
     * @param OptionDefinitionCollection $optionRepository
     */
    public function __construct(Definition $usageDefinition, OptionDefinitionCollection $optionRepository)
    {
        $this->usageDefinition  = $usageDefinition;
        $this->optionRepository = $optionRepository;
    }

    public function getDefinition()
    {
        return $this->usageDefinition;
    }

    public function getOptionDefinitions()
    {
        return $this->optionRepository;
    }

    /**
     * @param array $args
     *
     * @return Input|false
     */
    public function parse($args = null)
    {
        $fsm     = new BacktrackingRunner($this->usageDefinition->getInitialState(), new UsageParserContext());
        $symbols = $this->getInputSequence($args);

        if (!$symbols) {
            return false;
        }

        $result = $fsm->input($symbols);

        return $result === false ? false : new Input($result, $this->optionRepository);
    }

    private function getInputSequence($args = null)
    {
        if ($args instanceof InputSequence) {
            return $args;
        }

        return (new InputSequenceFactory())->createFrom($args, $this->optionRepository);
    }
}
