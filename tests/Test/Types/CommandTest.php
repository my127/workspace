<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use PHPUnit\Framework\TestCase;
use my127\Workspace\Tests\IntegrationTestCase;

class CommandTest extends IntegrationTestCase
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

        $this->assertEquals("Hello World", $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function php_can_be_used_as_an_interpreter()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
command('speak'): |
  #!php
  echo "Hello World";
EOD
        );

        $this->assertEquals("Hello World", $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function environment_variables_are_passed_through_to_the_bash_interpreter()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
command('speak'):
  env:
    MESSAGE: Sample Value
  exec: |
    #!bash
    echo -n "${MESSAGE}"
EOD
        );

        $this->assertEquals("Sample Value", $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function environment_variables_are_passed_through_to_the_php_intepreter()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
command('speak'):
  env:
    MESSAGE: Sample Value
  exec: |
    #!php
    echo getenv('MESSAGE');
EOD
        );

        $this->assertEquals("Sample Value", $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function working_directory_of_workspace_can_be_used_with_the_bash_interpreter()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
command('working-directory'): |
  #!bash(workspace:/test1)
  pwd
EOD
        );
        $this->workspace()->mkdir('test1');
        $this->workspace()->mkdir('test2');

        // even though we're running the command from test2 the script should still be executed within test1
        $this->assertEquals(
            $this->workspace()->path('test1') . "\n",
            $this->workspaceCommand('working-directory', 'test1')->getOutput()
        );
    }

    /** @test */
    public function working_directory_of_cwd_can_be_used_with_the_bash_interpreter()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
command('working-directory'): |
  #!bash(cwd:/)
  pwd
EOD
        );

        $this->workspace()->mkdir('test1');
        $this->workspace()->mkdir('test2');

        // even though we're running the command from test2 the script should still be executed within test1
        $this->assertEquals(
            $this->workspace()->path('test2') . "\n",
            $this->workspaceCommand('working-directory', 'test2')->getOutput()
        );
    }

    /** @test */
    public function working_directory_of_workspace_can_be_used_with_the_php_interpreter()
    {
        $path = $this->workspace()->put('workspace.yml', <<<'EOD'
command('working-directory'): |
  #!php(workspace:/test1)
  echo getcwd();
EOD
        );

        $this->workspace()->mkdir('test1');
        $this->workspace()->mkdir('test2');

        // even though we're running the command from test2 the script should still be executed within test1
        $this->assertEquals(
            $this->workspace()->path('test1'),
            $this->workspaceCommand('working-directory', 'test2')->getOutput()
        );
    }

    /** @test */
    public function working_directory_of_cwd_can_be_used_with_the_php_interpreter()
    {
        $path = $this->workspace()->put('workspace.yml', <<<'EOD'
command('working-directory'): |
  #!php(cwd:/)
  echo getcwd();
EOD
        );

        $this->workspace()->mkdir('test1');
        $this->workspace()->mkdir('test2');

        // even though we're running the command from test2 the script should still be executed within test1
        // TODO: this doesn't seem correct
        $this->assertEquals(
            $this->workspace()->path('test2'),
            $this->workspaceCommand('working-directory', 'test2')->getOutput()
        );
    }

    /** @test */
    public function attribute_filter_can_be_used_with_the_bash_interpreter()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
attribute('message'): Hello World

command('speak'): |
  #!bash|@
  echo -n "@('message')"
EOD
        );

        $this->assertEquals("Hello World", $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function attribute_filter_can_be_used_with_the_php_interpreter()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
attribute('message'): Hello World

command('speak'): |
  #!php|@
  echo "@('message')";
EOD
        );

        $this->assertEquals("Hello World", $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function expression_filter_can_be_used_with_the_bash_interpreter()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
attribute('message'): Hello

command('speak'): |
  #!bash|=
  echo -n "={ @('message') ~ ' ' ~ 'World' }"
EOD
        );

        $this->assertEquals("Hello World", $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function expression_filter_can_be_used_with_the_php_interpreter()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
attribute('message'): Hello

command('speak'): |
  #!php|=
  echo "={ @('message') ~ ' ' ~ 'World' }";
EOD
        );

        $this->assertEquals("Hello World", $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function environment_variable_values_can_be_expressions()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
attribute('message'): Hello

command('speak'):
  env:
    MESSAGE: = @('message') ~ ' ' ~ 'World'
  exec: |
    #!bash
    echo -n "${MESSAGE}"
EOD
        );

        $this->assertEquals("Hello World", $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function console_input_is_made_available_to_the_expression_filter()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
command('hello <name>'): |
  #!bash|=
  echo -n "hello ={ input.argument('name') }"
EOD
        );

        $this->assertEquals("hello world", $this->workspaceCommand('hello world')->getOutput());
    }

    /** @test */
    public function positional_commands_from_input_can_be_accessed()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
command('state (enable|disable)'): |
  #!bash|=
  echo -n "={ input.command(-1) }"
EOD
        );

        $this->assertEquals("disable", $this->workspaceCommand('state disable')->getOutput());
        $this->assertEquals("enable",  $this->workspaceCommand('state enable')->getOutput());
    }

    /** @test */
    public function console_input_can_be_used_as_expression_for_env_variable()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
command('hello <name>'):
  env:
    NAME: = input.argument('name')
  exec: |
    #!bash
    echo -n "hello ${NAME}"
EOD
        );

        $this->assertEquals("hello world", $this->workspaceCommand('hello world')->getOutput());
    }
}
