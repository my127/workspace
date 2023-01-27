<?php

namespace my127\Workspace\Tests\Test\Runner;

use my127\Console\Usage\Parser\InputSequence;
use PHPUnit\Framework\TestCase;

class InputSequenceTest extends TestCase
{
    public function testGetArgumentString(): void
    {
        self::assertEquals('', (new InputSequence([], []))->toArgumentString());
        self::assertEquals('foo bar', (new InputSequence([], ['bar', 'foo', 'ws']))->toArgumentString());
    }
}
