<?php

namespace my127\Console\Usage\Parser\Transition;

use my127\Console\Usage\Model\Argument;
use my127\Console\Usage\Parser\InputSequence;
use my127\Console\Usage\Parser\UsageParserContext;
use my127\FSM\Runner\BacktrackingRunner;
use my127\FSM\Runner\Runner;
use my127\FSM\State\State;
use my127\FSM\State\StateVisitor;
use my127\FSM\Stateful;
use my127\FSM\Transition\Transition;

class ArgumentTransition implements Transition
{
    /**
     * @var string
     */
    private $argument;

    /**
     * @var State
     */
    private $to;

    /**
     * @param $argument
     * @param $to
     */
    public function __construct($argument, $to)
    {
        $this->argument = $argument;
        $this->to = $to;
    }

    /**
     * {@inheritDoc}
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * {@inheritDoc}
     */
    public function setTo(State $state)
    {
        $this->to = $state;
    }

    /**
     * {@inheritDoc}
     */
    public function accept(StateVisitor $visitor, &$visited = [])
    {
        $this->to->accept($visitor, $visited);
    }

    /**
     * {@inheritDoc}
     *
     * @var InputSequence
     * @var UsageParserContext
     * @var BacktrackingRunner
     */
    public function can($input, Stateful $context, Runner $runner)
    {
        return $input->hasPositional();
    }

    /**
     * {@inheritDoc}
     *
     * @var InputSequence
     * @var UsageParserContext
     * @var BacktrackingRunner
     */
    public function apply($input, Stateful $context, Runner $runner)
    {
        $context->setCurrentState($this->to);

        return new Argument($this->argument, $input->pop());
    }

    /**
     * {@inheritDoc}
     */
    public function copy(&$visited = [])
    {
        return new self($this->argument, $this->to->copy($visited));
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return '<' . $this->argument . '>';
    }
}
