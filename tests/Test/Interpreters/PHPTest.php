<?php

namespace Test\my127\Workspace\Interpreters;

use Fixture;
use PHPUnit\Framework\TestCase;
use my127\Workspace\Tests\IntegrationTestCase;

class PHPTest extends IntegrationTestCase
{
    /** @test */
    public function helper_can_access_attributes_using_array_access_interface()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): Hello World

command('speak'): |
  #!php
  echo $ws['message'];
EOD
        );

        $this->assertEquals("Hello World", $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function helper_can_call_dynamically_declared_functions()
    {
        $this->createWorkspaceYml(<<<'EOD'

function('add', [v1, v2]): |
  #!php
  =$v1+$v2;

command('calculate'): |
  #!php
  echo $ws->add(2, 2);
EOD
        );

        $this->assertEquals("4", $this->workspaceCommand('calculate')->getOutput()
        );
    }

    /** @test */
    public function helper_can_run_declared_commands()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('cmdA'): |
  #!php
  echo $ws('cmdB');
  
command('cmdB'): |
  #!bash
  echo -n "Hello World"
EOD
        );

        $this->assertEquals("Hello World", $this->workspaceCommand('cmdA')->getOutput()
        );
    }
}
