<?php

namespace my127\Console\Usage\Parser\Transition;

use my127\Console\Usage\Parser\UsageParserContext;
use my127\FSM\Runner\BacktrackingRunner;
use my127\FSM\Runner\InputSequence;
use my127\FSM\Runner\Runner;
use my127\FSM\State\State;
use my127\FSM\State\StateVisitor;
use my127\FSM\Stateful;
use my127\FSM\Transition\Transition;

class LoopTransition implements Transition
{
    /**
     * @var State
     */
    private $to;

    public function __construct(State $to)
    {
        $this->to = $to;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function setTo(State $state)
    {
        $this->to = $state;
    }

    public function accept(StateVisitor $visitor, &$visited = [])
    {
        $this->to->accept($visitor, $visited);
    }

    /**
     * @var InputSequence
     * @var UsageParserContext
     * @var BacktrackingRunner
     */
    public function can($input, Stateful $context, Runner $runner)
    {
        $last = $context->getData($this);
        $current = $runner->getOutput();

        if ($last == $current) {
            return false;
        }

        $context->setData($this, $current);

        return true;
    }

    /**
     * @var InputSequence
     * @var UsageParserContext
     * @var BacktrackingRunner
     */
    public function apply($input, Stateful $context, Runner $runner)
    {
        $context->setCurrentState($this->to);

        return null;
    }

    public function copy(&$visited = [])
    {
        return new self($this->to->copy($visited));
    }

    public function __toString()
    {
        return 'loop';
    }
}
