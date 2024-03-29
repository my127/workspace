<?php

namespace my127\Console\Usage\Parser;

use my127\Console\Factory\OptionValueFactory;
use my127\Console\Usage\Input;
use my127\Console\Usage\Model\OptionDefinitionCollection;
use my127\FSM\Definition;
use my127\FSM\Runner\BacktrackingRunner;

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
     * @var OptionValueFactory
     */
    private $optionValueFactory;

    public function __construct(
        Definition $usageDefinition,
        OptionDefinitionCollection $optionRepository,
        OptionValueFactory $optionValueFactory
    ) {
        $this->usageDefinition = $usageDefinition;
        $this->optionRepository = $optionRepository;
        $this->optionValueFactory = $optionValueFactory;
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
        $fsm = new BacktrackingRunner($this->usageDefinition->getInitialState(), new UsageParserContext());
        $symbols = $this->getInputSequence($args);

        if (!$symbols) {
            return false;
        }

        $result = $fsm->input($symbols);

        return $result === false ? false : new Input($result, $this->optionRepository, $this->optionValueFactory);
    }

    private function getInputSequence($args = null)
    {
        if ($args instanceof InputSequence) {
            return $args;
        }

        return (new InputSequenceFactory())->createFrom($args, $this->optionRepository);
    }
}
