<?php

namespace my127\Workspace\FSM\Utility;

use my127\Workspace\FSM\State\State;
use my127\Workspace\FSM\State\StateVisitor;
use my127\Workspace\FSM\State\StateVisitorClient;
use my127\Workspace\FSM\Transition\Transition;

class DOT implements StateVisitor
{
    /**
     * @var State[]
     */
    private $states = [];

    /**
     * @var int
     */
    private $lastStateId = 0;

    /**
     * @var int
     */
    private $lastTransitionId = 0;

    /**
     * @var string
     */
    private $output = '';

    /**
     * State/Transition Graph to DOT
     */
    public function __construct()
    {
    }

    /**
     * Convert graph to DOT format
     *
     * @param StateVisitorClient $graph
     *
     * @return string
     */
    public function toDOT(StateVisitorClient $graph)
    {
        $this->reset();

        $this->write('digraph machine {');
        $graph->accept($this);
        $this->write('}');

        return $this->output;
    }

    /**
     * Visit State
     *
     * @param State $state
     *
     * @return void
     */
    public function visit(State $state)
    {
        $stateId = $this->getStateId($state);

        $attributes = [];
        $attributes['label'] = $stateId;
        $attributes['shape'] = 'circle';

        if ($state->isTerminal()) {
            $attributes['shape'] = 'doublecircle';
        }

        $this->write(
            sprintf(
                '%s ['.$this->formatAttributes($attributes).'];',
                $stateId,
                $stateId
            )
        );

        foreach ($state->getTransitions() as $transition) {
            $this->write(
                sprintf(
                    '%s -> %s [label="%s"];',
                    $stateId,
                    $this->getStateId($transition->getTo()),
                    $this->getTransitionId($transition).'('.(string)$transition.')'
                )
            );
        }
    }

    private function formatAttributes($attributes)
    {
        $formatted = [];

        foreach ($attributes as $k => $v) {
            $formatted[] = $k.'="'.$v.'"';
        }

        return implode(' ', $formatted);
    }

    /**
     * Append output
     *
     * @param string $output
     */
    private function write($output)
    {
        $this->output .= $output."\n";
    }

    /**
     * Prepare for outputting a new graph
     */
    private function reset()
    {
        $this->states           = [];
        $this->lastStateId      = 0;
        $this->lastTransitionId = 0;
        $this->output           = '';
    }

    /**
     * Get State ID
     *
     * @param State $state
     *
     * @return string
     */
    private function getStateId(State $state)
    {
        $hash = spl_object_hash($state);

        if (!isset($this->states[$hash])) {
            $this->states[$hash] = 'S'.(++$this->lastStateId);
        }

        return $this->states[$hash];
    }

    /**
     * Get Transition ID
     *
     * @param Transition $transition
     *
     * @return string
     */
    private function getTransitionId(Transition $transition)
    {
        return 'T'.(++$this->lastTransitionId);
    }
}
