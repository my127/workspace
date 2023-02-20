<?php

namespace Test\my127\Workspace\Interpreters;

use my127\Workspace\Tests\IntegrationTestCase;

class BashTest extends IntegrationTestCase
{
    /** @test */
    public function bashCanBeUsedAsAnInterpreter()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('speak'): |
  #!bash
  echo -n "Hello World"
EOD
        );

        $this->assertEquals(
            'Hello World',
            $this->workspaceCommand('speak')->getOutput()
        );
    }

    public function bashCanReceiveParentEnvironmentVariables()
    {
        putenv('MESSAGE=Hello World');
        $this->createWorkspaceYml(<<<'EOD'
command('speak'):
  exec: |
    #!bash
    echo -n "$MESSAGE"
EOD
        );

        $this->assertEquals(
            'Hello World',
            $this->workspaceCommand('speak')->getOutput()
        );
    }

    public function bashCanReceiveCustomEnvironmentVariables()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('speak'):
  env:
    MESSAGE: Hello World
  exec: |
    #!bash
    echo -n "$MESSAGE"
EOD
        );

        $this->assertEquals(
            'Hello World',
            $this->workspaceCommand('speak')->getOutput()
        );
    }

    public function bashCanReceiveParentEnvironmentVariablesWhileCustomUsed()
    {
        putenv('REAL_MESSAGE=Hello World');
        $this->createWorkspaceYml(<<<'EOD'
command('speak'):
  env:
    MESSAGE: I'm sorry Dave, I'm afraid I can't do that
  exec: |
    #!bash
    echo -n "$REAL_MESSAGE"
EOD
        );

        $this->assertEquals(
            'Hello World',
            $this->workspaceCommand('speak')->getOutput()
        );
    }

    public function bashCanReceiveOverrideParentEnvironmentVariables()
    {
        putenv('MESSAGE=I should be overridden');
        $this->createWorkspaceYml(<<<'EOD'
command('speak'):
  env:
    MESSAGE: Hello World
  exec: |
    #!bash
    echo -n "$MESSAGE"
EOD
        );

        $this->assertEquals(
            'Hello World',
            $this->workspaceCommand('speak')->getOutput()
        );
    }

    public function bashCanReceiveQuotesInEnvironmentVariables()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('speak'):
  env:
    MESSAGE: someone said 'Hello World'
  exec: |
    #!bash
    echo -n "$MESSAGE"
EOD
        );

        $this->assertEquals(
            'someone said \'Hello World\'',
            $this->workspaceCommand('speak')->getOutput()
        );
    }
}
