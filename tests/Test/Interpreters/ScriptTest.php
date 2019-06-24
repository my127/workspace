<?php

namespace Test\my127\Workspace\Interpreters;

use Fixture;
use PHPUnit\Framework\TestCase;
use my127\Workspace\Interpreter\Script;
use my127\Workspace\Interpreter\Executor;

class ScriptTest extends TestCase
{
    /** @test */
    public function exec_will_pass_equal_number_of_args()
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
    public function exec_will_pass_null_values_when_less_args_provided()
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
