<?php

namespace Test\my127\Workspace\Interpreters;

use Fixture;
use PHPUnit\Framework\TestCase;

class BashTest extends TestCase
{
    /** @test */
    public function bash_can_be_used_as_an_interpreter()
    {
        Fixture::workspace(<<<'EOD'
command('speak'): |
  #!bash
  echo -n "Hello World"
EOD
        );

        $this->assertEquals("Hello World", run('speak'));
    }
}
