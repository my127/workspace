<?php

namespace Test\my127\FSM\Runner;

use my127\FSM\Context;
use my127\FSM\State\DefaultState;
use my127\FSM\Runner\SequenceRunner;
use my127\FSM\State\State;
use PHPUnit\Framework\TestCase;

class SequenceRunnerTest extends TestCase
{
    /**
     * @var SequenceRunner
     */
    private $fsm;

    protected function setUp(): void
    {
        $s1 = new DefaultState('S1', State::TYPE_INITIAL);
        $s2 = new DefaultState('S2');
        $s3 = new DefaultState('S3');
        $s4 = new DefaultState('S4', State::TYPE_TERMINAL);

        $s1->addTransition('t1', $s2);
        $s1->addTransition('t2', $s3);
        $s2->addTransition('t3', $s4);
        $s4->addTransition('t4', $s4);

        $this->fsm = new SequenceRunner($s1, new Context());
    }

    /**
     * @test
     */
    public function it_iterates_the_fsm_with_each_of_the_given_inputs_returning_the_successful_vector_when_valid()
    {
        $this->assertEquals(['S2', 'S4'], $this->fsm->input(['t1', 't3']));
    }

    /**
     * @test
     */
    public function it_returns_false_given_invalid_input()
    {
        $this->assertEquals(false, $this->fsm->input(['t1', 't2']));
    }

    /**
     * @test
     */
    public function it_must_end_on_a_terminal_state_to_return_result()
    {
        $this->assertEquals(['S2', 'S4'], $this->fsm->input(['t1', 't3']));
        $this->assertEquals(false, $this->fsm->input(['t1']));
    }
}