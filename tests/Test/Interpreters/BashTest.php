<?php

namespace Test\my127\Workspace\Interpreters;

use my127\Workspace\Tests\IntegrationTestCase;

class BashTest extends IntegrationTestCase
{
    /** @test */
    public function bashCanBeUsedAsAnInterpreter(): void
    {
        $this->createWorkspaceYml(
            <<<'EOD'
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
}
