<?php

namespace Test\my127\Workspace\Interpreters;

use Fixture;
use PHPUnit\Framework\TestCase;

class PHPTest extends TestCase
{
    /** @test */
    public function helper_can_access_attributes_using_array_access_interface()
    {
        Fixture::workspace(<<<'EOD'
attribute('message'): Hello World

command('speak'): |
  #!php
  echo $ws['message'];
EOD
        );

        $this->assertEquals("Hello World", run('speak'));
    }

    /** @test */
    public function helper_can_call_dynamically_declared_functions()
    {
        Fixture::workspace(<<<'EOD'

function('add', [v1, v2]): |
  #!php
  =$v1+$v2;

command('calculate'): |
  #!php
  echo $ws->add(2, 2);
EOD
        );

        $this->assertEquals("4", run('calculate'));
    }

    /** @test */
    public function helper_can_run_declared_commands()
    {
        Fixture::workspace(<<<'EOD'
command('cmdA'): |
  #!php
  echo $ws('cmdB');
  
command('cmdB'): |
  #!bash
  echo -n "Hello World"
EOD
        );

        $this->assertEquals("Hello World", run('cmdA'));
    }
}
