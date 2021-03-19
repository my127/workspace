<?php

namespace Test\my127\Workspace\Interpreters;

use Fixture;
use PHPUnit\Framework\TestCase;
use my127\Workspace\Tests\IntegrationTestCase;

class BashTest extends IntegrationTestCase
{
    /** @test */
    public function bash_can_be_used_as_an_interpreter()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
command('speak'): |
  #!bash
  echo -n "Hello World"
EOD
        );

        $this->assertEquals(
            "Hello World",
            $this->workspaceCommand('speak')->getOutput()
        );
    }
}
