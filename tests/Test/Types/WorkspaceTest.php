<?php

namespace Test\my127\Workspace\Types;

use my127\Workspace\Tests\IntegrationTestCase;

class WorkspaceTest extends IntegrationTestCase
{
    /** @test */
    public function workspaceDeclarationIsOptional()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('hi'): |
  #!bash
  echo -n "Hello World"
EOD
        );

        $this->assertEquals('Hello World', $this->workspaceCommand('hi')->getOutput());
    }

    /** @test */
    public function workspaceNameIsMadeAvailableAsAttribute()
    {
        $this->createWorkspaceYml(<<<'EOD'
workspace('acme'): ~

command('get workspace name'): |
  #!bash|@
  echo -n "@('workspace.name')"
EOD
        );

        $this->assertEquals('acme', $this->workspaceCommand('get workspace name')->getOutput());
    }

    /** @test */
    public function workspaceDescriptionIsMadeAvailableAsAttribute()
    {
        $this->createWorkspaceYml(<<<'EOD'
workspace('acme'):
  description: Example description

command('get workspace description'): |
  #!bash|@
  echo -n "@('workspace.description')"
EOD
        );

        $this->assertEquals('Example description', $this->workspaceCommand('get workspace description')->getOutput());
    }

    /** @test */
    public function namespaceAttributeIsMadeAvailableAndDefaultsToWorkspaceName()
    {
        $this->createWorkspaceYml(<<<'EOD'
workspace('acme'): ~

command('get namespace'): |
  #!bash|@
  echo -n "@('namespace')"
EOD
        );

        $this->assertEquals('acme', $this->workspaceCommand('get namespace')->getOutput());
    }

    /** @test */
    public function whenNotDeclaredWorkspaceNameIsBasenameOfContainingDirectory()
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
    public function workspaceExecMethodIsMadeAvailableToExpressions()
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
    public function phpPassthruIsAvailableToTheWorkspaceHelper()
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
