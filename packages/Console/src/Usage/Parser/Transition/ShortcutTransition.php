<?php

namespace my127\Console\Usage\Parser\Transition;

use my127\FSM\Runner\Runner;
use my127\FSM\State\State;
use my127\FSM\State\StateVisitor;
use my127\FSM\Stateful;
use my127\FSM\Transition\Transition;

class ShortcutTransition implements Transition
{
    /**
     * @var State
     */
    private $to;

    public function __construct(State $to)
    {
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
     */
    public function can($input, Stateful $context, Runner $runner)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function apply($input, Stateful $context, Runner $runner)
    {
        $context->setCurrentState($this->to);

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function copy(&$visited = [])
    {
        return new self($this->to->copy($visited));
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return '*';
    }
}
