<?php

namespace my127\Workspace\Tests\Test\Runner;

use PHPUnit\Framework\TestCase;
use my127\Console\Usage\Parser\InputSequence;

class InputSequenceTest extends TestCase
{
    public function testGetArgumentString(): void
    {
        self::assertEquals('', (new InputSequence([], []))->toArgumentString());
        self::assertEquals('foo bar', (new InputSequence([], ['bar' ,'foo', 'ws']))->toArgumentString());
    }
}
