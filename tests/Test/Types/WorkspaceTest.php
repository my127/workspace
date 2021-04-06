<?php

namespace Test\my127\Workspace\Types;

use PHPUnit\Framework\TestCase;
use my127\Workspace\Tests\IntegrationTestCase;

class WorkspaceTest extends IntegrationTestCase
{
    /** @test */
    public function workspace_declaration_is_optional()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('hi'): |
  #!bash
  echo -n "Hello World"
EOD
        );

        $this->assertEquals("Hello World", $this->workspaceCommand('hi')->getOutput());
    }

    /** @test */
    public function workspace_name_is_made_available_as_attribute()
    {
        $this->createWorkspaceYml(<<<'EOD'
workspace('acme'): ~

command('get workspace name'): |
  #!bash|@
  echo -n "@('workspace.name')"
EOD
        );

        $this->assertEquals("acme", $this->workspaceCommand('get workspace name')->getOutput());
    }

    /** @test */
    public function workspace_description_is_made_available_as_attribute()
    {
        $this->createWorkspaceYml(<<<'EOD'
workspace('acme'):
  description: Example description

command('get workspace description'): |
  #!bash|@
  echo -n "@('workspace.description')"
EOD
        );

        $this->assertEquals("Example description", $this->workspaceCommand('get workspace description')->getOutput());
    }

    /** @test */
    public function namespace_attribute_is_made_available_and_defaults_to_workspace_name()
    {
        $this->createWorkspaceYml(<<<'EOD'
workspace('acme'): ~

command('get namespace'): |
  #!bash|@
  echo -n "@('namespace')"
EOD
        );

        $this->assertEquals("acme", $this->workspaceCommand('get namespace')->getOutput());
    }

    /** @test */
    public function when_not_declared_workspace_name_is_basename_of_containing_directory()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('get workspace name'): |
  #!bash|@
  echo -n "@('workspace.name')"
EOD
        );

        $this->assertEquals(basename($this->workspace()->path()), $this->workspaceCommand('get workspace name')->getOutput());
    }

    /** @test */
    public function workspace_exec_method_is_made_available_to_expressions()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): = exec("echo 'Hello World'")
        
command('speak'): |
  #!bash|@
  echo "@('message')"
EOD
        );

        $this->assertEquals("Hello World\n", $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function php_passthru_is_available_to_the_workspace_helper()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('speak'): |
  #!php
  $ws->passthru('echo "Hello World"');
EOD
        );

        $this->assertEquals("Hello World\n", $this->workspaceCommand('speak')->getOutput());
    }
}
