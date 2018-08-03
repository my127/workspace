<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    /** @test */
    public function bash_hello_world()
    {
        Fixture::workspace('command/basic');

        $this->assertEquals("Hello World", run('speak'));
    }
}