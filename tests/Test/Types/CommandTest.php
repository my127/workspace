<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
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

    /** @test */
    public function php_can_be_used_as_an_interpreter()
    {
        Fixture::workspace(<<<'EOD'
command('speak'): |
  #!php
  echo "Hello World";
EOD
        );

        $this->assertEquals("Hello World", run('speak'));
    }

    /** @test */
    public function environment_variables_are_passed_through_to_the_bash_interpreter()
    {
        Fixture::workspace(<<<'EOD'
command('speak'):
  env:
    MESSAGE: Sample Value
  exec: |
    #!bash
    echo -n "${MESSAGE}"
EOD
        );

        $this->assertEquals("Sample Value", run('speak'));
    }

    /** @test */
    public function environment_variables_are_passed_through_to_the_php_intepreter()
    {
        Fixture::workspace(<<<'EOD'
command('speak'):
  env:
    MESSAGE: Sample Value
  exec: |
    #!php
    echo getenv('MESSAGE');
EOD
        );

        $this->assertEquals("Sample Value", run('speak'));
    }

    /** @test */
    public function working_directory_of_workspace_can_be_used_with_the_bash_interpreter()
    {
        $path = Fixture::workspace(<<<'EOD'
command('working-directory'): |
  #!bash(workspace:/test1)
  pwd
EOD
        );

        mkdir($path.'/test1');
        mkdir($path.'/test2');

        chdir($path.'/test2');

        // even though we're running the command from test2 the script should still be executed within test1
        $this->assertEquals($path.'/test1'."\n", run('working-directory'));
    }

    /** @test */
    public function working_directory_of_cwd_can_be_used_with_the_bash_interpreter()
    {
        $path = Fixture::workspace(<<<'EOD'
command('working-directory'): |
  #!bash(cwd:/)
  pwd
EOD
        );

        mkdir($path.'/test1');
        mkdir($path.'/test2');

        chdir($path.'/test2');

        $this->assertEquals($path.'/test2'."\n", run('working-directory'));
    }

    /** @test */
    public function working_directory_of_workspace_can_be_used_with_the_php_interpreter()
    {
        $path = Fixture::workspace(<<<'EOD'
command('working-directory'): |
  #!php(workspace:/test1)
  echo getcwd();
EOD
        );

        mkdir($path.'/test1');
        mkdir($path.'/test2');

        chdir($path.'/test2');

        // even though we're running the command from test2 the script should still be executed within test1
        $this->assertEquals($path.'/test1', run('working-directory'));
    }

    /** @test */
    public function working_directory_of_cwd_can_be_used_with_the_php_interpreter()
    {
        $path = Fixture::workspace(<<<'EOD'
command('working-directory'): |
  #!php(cwd:/)
  echo getcwd();
EOD
        );

        mkdir($path.'/test1');
        mkdir($path.'/test2');

        chdir($path.'/test2');

        $this->assertEquals($path.'/test2', run('working-directory'));
    }

    /** @test */
    public function attribute_filter_can_be_used_with_the_bash_interpreter()
    {
        Fixture::workspace(<<<'EOD'
attribute('message'): Hello World

command('speak'): |
  #!bash|@
  echo -n "@('message')"
EOD
        );

        $this->assertEquals("Hello World", run('speak'));
    }

    /** @test */
    public function attribute_filter_can_be_used_with_the_php_interpreter()
    {
        Fixture::workspace(<<<'EOD'
attribute('message'): Hello World

command('speak'): |
  #!php|@
  echo "@('message')";
EOD
        );

        $this->assertEquals("Hello World", run('speak'));
    }

    /** @test */
    public function expression_filter_can_be_used_with_the_bash_interpreter()
    {
        Fixture::workspace(<<<'EOD'
attribute('message'): Hello

command('speak'): |
  #!bash|=
  echo -n "={ @('message') ~ ' ' ~ 'World' }"
EOD
        );

        $this->assertEquals("Hello World", run('speak'));
    }

    /** @test */
    public function expression_filter_can_be_used_with_the_php_interpreter()
    {
        Fixture::workspace(<<<'EOD'
attribute('message'): Hello

command('speak'): |
  #!php|=
  echo "={ @('message') ~ ' ' ~ 'World' }";
EOD
        );

        $this->assertEquals("Hello World", run('speak'));
    }

    /** @test */
    public function environment_variable_values_can_be_expressions()
    {
        Fixture::workspace(<<<'EOD'
attribute('message'): Hello

command('speak'):
  env:
    MESSAGE: = @('message') ~ ' ' ~ 'World'
  exec: |
    #!bash
    echo -n "${MESSAGE}"
EOD
        );

        $this->assertEquals("Hello World", run('speak'));
    }

    /** @test */
    public function console_input_is_made_available_to_the_expression_filter()
    {
        Fixture::workspace(<<<'EOD'
command('hello <name>'): |
  #!bash|=
  echo -n "hello ={ input.argument('name') }"
EOD
        );

        $this->assertEquals("hello world", run('hello world'));
    }

    /** @test */
    public function positional_commands_from_input_can_be_accessed()
    {
        Fixture::workspace(<<<'EOD'
command('state (enable|disable)'): |
  #!bash|=
  echo -n "={ input.command(-1) }"
EOD
        );

        $this->assertEquals("disable", run('state disable'));
        $this->assertEquals("enable",  run('state enable'));
    }

    /** @test */
    public function console_input_can_be_used_as_expression_for_env_variable()
    {
        Fixture::workspace(<<<'EOD'
command('hello <name>'):
  env:
    NAME: = input.argument('name')
  exec: |
    #!bash
    echo -n "hello ${NAME}"
EOD
        );

        $this->assertEquals("hello world", run('hello world'));
    }
}
