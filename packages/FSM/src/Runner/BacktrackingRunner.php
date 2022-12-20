<?php

namespace my127\FSM\Runner;

use my127\FSM\Context;
use my127\FSM\State\State;
use my127\FSM\Stateful;
use my127\FSM\Transition\Transition;
use my127\FSM\Utility\GraphBuilder;

/**
 * Class BacktrackingRunner.
 */
class BacktrackingRunner implements Runner
{
    /**
     * Current Input.
     *
     * @var mixed
     */
    private $input;

    /**
     * Current Context.
     *
     * @var Stateful
     */
    private $context;

    /**
     * Current Output.
     *
     * @var mixed[]
     */
    private $output = [];

    /**
     * Stack.
     *
     * @var mixed[][]
     */
    private $stack = [];

    /**
     * @var GraphBuilder
     */
    private $graph;

    private $bt = [];

    /**
     * Backtracking Runner.
     *
     * Similar to SequenceRunner but takes a backtracking approach to guarantee finding
     * a correct path if one exists with the given input sequence.
     *
     * Note:
     *
     * The first solution encountered is returned, additional solutions may exist but no attempt will be made
     * to find them.
     *
     * As we need to backtrack consider how you're context object changes during transitions, you may need
     * a customised __clone().
     *
     * This isn't efficient :) If your FSM is large or very recursive then you're going to run out of memory quickly...
     *
     * @param Stateful $context
     */
    public function __construct(State $initialState, Stateful $context = null)
    {
        $this->context = $context ?: new Context();
        $this->context->setCurrentState($initialState);
        $this->graph = new GraphBuilder($initialState);
    }

    /**
     * Input.
     *
     * @param mixed $input An array of symbols to pass into the FSM
     *
     * @return mixed[]|false
     */
    public function input($input)
    {
        $this->input = is_array($input) ? new InputSequence($input) : $input;
        $solution = $this->backtrack();

        return $solution !== null ? $solution : false;
    }

    /**
     * Get Lambda.
     *
     * @return callable
     */
    public function getLambda()
    {
        return [$this, 'input'];
    }

    /**
     * Current Output.
     *
     * @return mixed[]
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Backtrack.
     */
    private function backtrack()
    {
        if ($this->accept()) {
            return $this->output;
        }

        foreach ($this->candidates() as $candidate) {
            if (!$candidate->can($this->input, $this->context, $this)) {
                continue;
            }

            $this->push();

            $this->bt[] = $this->graph->transitions[$candidate];
            // echo implode(' ', $this->bt)."\n";

            $solved = $this->apply($candidate);
            $this->pop();

            if ($solved !== null) {
                return $solved;
            }
        }

        return null;
    }

    /**
     * Push.
     *
     * @return void
     */
    private function push()
    {
        $this->stack[] = [
            clone $this->input,
            $this->output,
            clone $this->context,
        ];
    }

    /**
     * Pop.
     *
     * @return void
     */
    private function pop()
    {
        array_pop($this->bt);
        // echo implode(' ', $this->bt)."\n";
        list($this->input, $this->output, $this->context)
            = array_pop($this->stack);
    }

    /**
     * Apply Transition.
     *
     * @param Transition $candidate
     *
     * @return mixed[]|null
     */
    private function apply($candidate)
    {
        if (($output = $candidate->apply($this->input, $this->context, $this)) !== null) {
            $this->output[] = $output;
        }

        return $this->backtrack();
    }

    /**
     * Candidates.
     *
     * @return Transition[]
     */
    private function candidates()
    {
        return $this->context->getCurrentState()->getTransitions();
    }

    /**
     * Accept.
     *
     * Has the FSM reached a terminal state with all input consumed?
     *
     * @return bool
     */
    private function accept()
    {
        if ($this->context->getCurrentState()->isTerminal() && count($this->input) == 0) {
            return true;
        }

        return false;
    }

    /**
     * Alias of input.
     *
     * @return mixed
     */
    public function __invoke($input)
    {
        return $this->input($input);
    }

    /**
     * Get Current State.
     *
     * @return State
     */
    public function getCurrentState()
    {
        return $this->context->getCurrentState();
    }
}
