<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use PHPUnit\Framework\TestCase;

class WorkspaceTest extends TestCase
{
    /** @test */
    public function workspace_declaration_is_optional()
    {
        Fixture::workspace(<<<'EOD'
command('hi'): |
  #!bash
  echo -n "Hello World"
EOD
        );

        $this->assertEquals("Hello World", run('hi'));
    }

    /** @test */
    public function workspace_name_is_made_available_as_attribute()
    {
        Fixture::workspace(<<<'EOD'
workspace('acme'): ~

command('get workspace name'): |
  #!bash|@
  echo -n "@('workspace.name')"
EOD
        );

        $this->assertEquals("acme", run('get workspace name'));
    }

    /** @test */
    public function workspace_description_is_made_available_as_attribute()
    {
        Fixture::workspace(<<<'EOD'
workspace('acme'):
  description: Example description

command('get workspace description'): |
  #!bash|@
  echo -n "@('workspace.description')"
EOD
        );

        $this->assertEquals("Example description", run('get workspace description'));
    }

    /** @test */
    public function namespace_attribute_is_made_available_and_defaults_to_workspace_name()
    {
        Fixture::workspace(<<<'EOD'
workspace('acme'): ~

command('get namespace'): |
  #!bash|@
  echo -n "@('namespace')"
EOD
        );

        $this->assertEquals("acme", run('get namespace'));
    }

    /** @test */
    public function when_not_declared_workspace_name_is_basename_of_containing_directory()
    {
        $path = Fixture::workspace(<<<'EOD'
command('get workspace name'): |
  #!bash|@
  echo -n "@('workspace.name')"
EOD
        );

        $this->assertEquals(basename($path), run('get workspace name'));
    }

    /** @test */
    public function workspace_exec_method_is_made_available_to_expressions()
    {
        Fixture::workspace(<<<'EOD'
attribute('message'): = exec("echo 'Hello World'")
        
command('speak'): |
  #!bash|@
  echo "@('message')"
EOD
        );

        $this->assertEquals("Hello World\n", run('speak'));
    }

    /** @test */
    public function php_passthru_is_available_to_the_workspace_helper()
    {
        Fixture::workspace(<<<'EOD'
command('speak'): |
  #!php
  $ws->passthru('echo "Hello World"');
EOD
        );

        $this->assertEquals("Hello World\n", run('speak'));
    }
}
