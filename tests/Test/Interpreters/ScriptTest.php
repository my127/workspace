<?php

namespace Test\my127\Workspace\Interpreters;

use my127\Workspace\Interpreter\Executor;
use my127\Workspace\Interpreter\Script;
use PHPUnit\Framework\TestCase;

class ScriptTest extends TestCase
{
    /** @test */
    public function execWillPassEqualNumberOfArgs()
    {
        $executor = $this->getMockBuilder(Executor::class)->getMock();

        $expectedArgs = [
            'arg1' => 'val1',
            'arg2' => 'val2',
        ];

        $assert = $this;
        $executor->expects($this->atLeastOnce())
            ->method('exec')
            ->willReturnCallback(
                function ($script, $args, $cwd, $env) use ($assert, $expectedArgs) {
                    $assert->assertEquals($expectedArgs, $args);
                }
            );

        $script = new Script($executor, '/path', '', ['arg1', 'arg2']);
        $script->exec(['val1', 'val2'], []);
    }

    /** @test */
    public function execWillPassNullValuesWhenLessArgsProvided()
    {
        $executor = $this->getMockBuilder(Executor::class)->getMock();

        $expectedArgs = [
            'arg1' => 'val1',
            'arg2' => null,
        ];

        $assert = $this;
        $executor->expects($this->atLeastOnce())
            ->method('exec')
            ->willReturnCallback(
                function ($script, $args, $cwd, $env) use ($assert, $expectedArgs) {
                    $assert->assertEquals($expectedArgs, $args);
                }
            );

        $script = new Script($executor, '/path', '', ['arg1', 'arg2']);
        $script->exec(['val1'], []);
    }
}
