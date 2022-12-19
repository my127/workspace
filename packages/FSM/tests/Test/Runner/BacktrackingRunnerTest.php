<?php

namespace Test\my127\FSM\Runner;

use my127\FSM\Context;
use my127\FSM\Runner\BacktrackingRunner;
use my127\FSM\State\DefaultState;
use my127\FSM\State\State;
use PHPUnit\Framework\TestCase;

class BacktrackingRunnerTest extends TestCase
{
    /**
     * @var BacktrackingRunner
     */
    private $fsm;

    protected function setUp(): void
    {
        $s1 = new DefaultState('S1', State::TYPE_INITIAL);
        $s2 = new DefaultState('S2');
        $s3 = new DefaultState('S3');
        $s4 = new DefaultState('S4');
        $s5 = new DefaultState('S5', State::TYPE_TERMINAL);

        $s1->addTransition('t1', $s2);
        $s1->addTransition('t1', $s3);
        $s2->addTransition('t2', $s4);
        $s3->addTransition('t2', $s5);

        $this->fsm = new BacktrackingRunner($s1, new Context());
    }

    /**
     * @test
     */
    public function itFindsTheCorrectPathToReachATerminalStateGivenValidInput()
    {
        $this->assertEquals(['S3', 'S5'], $this->fsm->input(['t1', 't2']));
    }

    /**
     * @test
     */
    public function itReturnsFalseGivenInvalidInput()
    {
        $this->assertEquals(false, $this->fsm->input(['t1', 't3']));
    }

    /**
     * @test
     */
    public function itMustEndOnATerminalStateToReturnResult()
    {
        $this->assertEquals(['S3', 'S5'], $this->fsm->input(['t1', 't2']));
        $this->assertEquals(false, $this->fsm->input(['t1']));
    }
}
