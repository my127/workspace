<?php

namespace Test\my127\FSM\Runner;

use my127\FSM\Context;
use my127\FSM\Runner\SequenceRunner;
use my127\FSM\State\DefaultState;
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
    public function itIteratesTheFsmWithEachOfTheGivenInputsReturningTheSuccessfulVectorWhenValid()
    {
        $this->assertEquals(['S2', 'S4'], $this->fsm->input(['t1', 't3']));
    }

    /**
     * @test
     */
    public function itReturnsFalseGivenInvalidInput()
    {
        $this->assertEquals(false, $this->fsm->input(['t1', 't2']));
    }

    /**
     * @test
     */
    public function itMustEndOnATerminalStateToReturnResult()
    {
        $this->assertEquals(['S2', 'S4'], $this->fsm->input(['t1', 't3']));
        $this->assertEquals(false, $this->fsm->input(['t1']));
    }
}
