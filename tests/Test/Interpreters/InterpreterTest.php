<?php

namespace Test\my127\Workspace\Interpreters;

use my127\Workspace\Tests\IntegrationTestCase;

class InterpreterTest extends IntegrationTestCase
{
    /** @test */
    public function exceptionWhenInterpreterMissing()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('speak'): |
  echo -n "Hello World"
EOD
        );

        $this->expectException('Exception');
        $this->expectExceptionMessageMatches('/does not specify an interpreter/');
        $this->workspaceCommand('speak');
    }
}
