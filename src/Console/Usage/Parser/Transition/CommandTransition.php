<?php

namespace my127\Workspace\Console\Usage\Parser\Transition;

use my127\Workspace\Console\Usage\Model\Command;
use my127\Workspace\Console\Usage\Parser\UsageParserContext;
use my127\Workspace\FSM\Runner\BacktrackingRunner;
use my127\Workspace\FSM\Runner\InputSequence;
use my127\Workspace\FSM\Runner\Runner;
use my127\Workspace\FSM\State\State;
use my127\Workspace\FSM\Stateful;
use my127\Workspace\FSM\State\StateVisitor;
use my127\Workspace\FSM\Transition\Transition;
use my127\Workspace\Console\Usage\Parser\UsageParserContext;
use my127\Workspace\FSM\Runner\BacktrackingRunner;
use my127\Workspace\FSM\Runner\InputSequence;

class CommandTransition implements Transition
{
    /**
     * @var string
     */
    private $command;

    /**
     * @var State
     */
    private $to;

    /**
     * @param $command
     * @param $to
     */
    public function __construct($command, $to)
    {
        $this->command = $command;
        $this->to = $to;
    }

    /**
     * @inheritDoc
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @inheritDoc
     */
    public function setTo(State $state)
    {
        $this->to = $state;
    }

    /**
     * @inheritDoc
     */
    public function accept(StateVisitor $visitor, &$visited = [])
    {
        $this->to->accept($visitor, $visited);
    }

    /**
     * @inheritDoc
     *
     * @var InputSequence      $input
     * @var UsageParserContext $context
     * @var BacktrackingRunner $runner
     */
    public function can($input, Stateful $context, Runner $runner)
    {
        return $input->peek() == $this->command;
    }

    /**
     * @inheritDoc
     *
     * @var InputSequence      $input
     * @var UsageParserContext $context
     * @var BacktrackingRunner $runner
     */
    public function apply($input, Stateful $context, Runner $runner)
    {
        $context->setCurrentState($this->to);

        return new Command($input->pop());
    }

    /**
     * @inheritDoc
     */
    public function copy(&$visited = [])
    {
        return new self($this->command, $this->to->copy($visited));
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->command;
    }
}
