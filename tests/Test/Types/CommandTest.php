<?php

namespace Test\my127\Workspace\Types;

use my127\Workspace\Tests\IntegrationTestCase;

class CommandTest extends IntegrationTestCase
{
    /** @test */
    public function bashCanBeUsedAsAnInterpreter()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('speak'): |
  #!bash
  echo -n "Hello World"
EOD
        );

        $this->assertEquals('Hello World', $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function phpCanBeUsedAsAnInterpreter()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('speak'): |
  #!php
  echo "Hello World";
EOD
        );

        $this->assertEquals('Hello World', $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function environmentVariablesArePassedThroughToTheBashInterpreter()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('speak'):
  env:
    MESSAGE: Sample Value
  exec: |
    #!bash
    echo -n "${MESSAGE}"
EOD
        );

        $this->assertEquals('Sample Value', $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function environmentVariablesArePassedThroughToThePhpIntepreter()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('speak'):
  env:
    MESSAGE: Sample Value
  exec: |
    #!php
    echo getenv('MESSAGE');
EOD
        );

        $this->assertEquals('Sample Value', $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function workingDirectoryOfWorkspaceCanBeUsedWithTheBashInterpreter()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('working-directory'): |
  #!bash(workspace:/test1)
  pwd
EOD
        );
        $this->workspace()->mkdir('test1');
        $this->workspace()->mkdir('test2');

        // even though we're running the command from test2 the script should still be executed within test1
        $this->assertEquals(
            $this->workspace()->path('test1')."\n",
            $this->workspaceCommand('working-directory', 'test1')->getOutput()
        );
    }

    /** @test */
    public function workingDirectoryOfCwdCanBeUsedWithTheBashInterpreter()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('working-directory'): |
  #!bash(cwd:/)
  pwd
EOD
        );

        $this->workspace()->mkdir('test1');
        $this->workspace()->mkdir('test2');

        // even though we're running the command from test2 the script should still be executed within test1
        $this->assertEquals(
            $this->workspace()->path('test2')."\n",
            $this->workspaceCommand('working-directory', 'test2')->getOutput()
        );
    }

    /** @test */
    public function workingDirectoryOfWorkspaceCanBeUsedWithThePhpInterpreter()
    {
        $path = $this->createWorkspaceYml(<<<'EOD'
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
    public function workingDirectoryOfCwdCanBeUsedWithThePhpInterpreter()
    {
        $path = $this->createWorkspaceYml(<<<'EOD'
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
    public function attributeFilterCanBeUsedWithTheBashInterpreter()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): Hello World

command('speak'): |
  #!bash|@
  echo -n "@('message')"
EOD
        );

        $this->assertEquals('Hello World', $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function attributeFilterCanBeUsedWithThePhpInterpreter()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): Hello World

command('speak'): |
  #!php|@
  echo "@('message')";
EOD
        );

        $this->assertEquals('Hello World', $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function expressionFilterCanBeUsedWithTheBashInterpreter()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): Hello

command('speak'): |
  #!bash|=
  echo -n "={ @('message') ~ ' ' ~ 'World' }"
EOD
        );

        $this->assertEquals('Hello World', $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function expressionFilterCanBeUsedWithThePhpInterpreter()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): Hello

command('speak'): |
  #!php|=
  echo "={ @('message') ~ ' ' ~ 'World' }";
EOD
        );

        $this->assertEquals('Hello World', $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function environmentVariableValuesCanBeExpressions()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): Hello

command('speak'):
  env:
    MESSAGE: = @('message') ~ ' ' ~ 'World'
  exec: |
    #!bash
    echo -n "${MESSAGE}"
EOD
        );

        $this->assertEquals('Hello World', $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function consoleInputIsMadeAvailableToTheExpressionFilter()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('hello <name>'): |
  #!bash|=
  echo -n "hello ={ input.argument('name') }"
EOD
        );

        $this->assertEquals('hello world', $this->workspaceCommand('hello world')->getOutput());
    }

    /** @test */
    public function positionalCommandsFromInputCanBeAccessed()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('state (enable|disable)'): |
  #!bash|=
  echo -n "={ input.command(-1) }"
EOD
        );

        $this->assertEquals('disable', $this->workspaceCommand('state disable')->getOutput());
        $this->assertEquals('enable', $this->workspaceCommand('state enable')->getOutput());
    }

    /** @test */
    public function consoleInputCanBeUsedAsExpressionForEnvVariable()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('hello <name>'):
  env:
    NAME: = input.argument('name')
  exec: |
    #!bash
    echo -n "hello ${NAME}"
EOD
        );

        $this->assertEquals('hello world', $this->workspaceCommand('hello world')->getOutput());
    }

    /** @test */
    public function commandDescriptionIsOutputWithHelp()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('speak'):
  description: This command speaks
  exec: |
    #!bash
    true
EOD
        );

        $this->assertMatchesRegularExpression(
            '/^\s*This command speaks\s+Usage:/s',
            $this->removeAnsiColorEscapes($this->workspaceCommand('speak --help')->getOutput())
        );
    }

    /** @test */
    public function commandDescriptionDefaultIsOutputWithHelpWhenDescriptionIsMissing()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('speak'):
  exec: |
    #!bash
    true
EOD
        );

        $this->assertMatchesRegularExpression(
            '/^\s*ws speak\s+Usage:/s',
            $this->removeAnsiColorEscapes($this->workspaceCommand('speak --help')->getOutput())
        );
    }

    /** @test */
    public function subcommandDescriptionIsOutputWithHelp()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('speak'):
  exec: |
    #!bash
    true
command('speak subcommand'):
  description: Subcommand speaking
  exec: |
    #!bash
    true
EOD
        );

        $this->assertMatchesRegularExpression(
            '/Sub Commands:.*subcommand\s+Subcommand speaking.*Global Options:/s',
            $this->removeAnsiColorEscapes($this->workspaceCommand('speak --help')->getOutput())
        );
    }

    /** @test */
    public function optionalArgumentsCanBeSpecified()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('default_value'): 'default value'

command('hello [<name>]'):
  exec: |
    #!bash|=
    echo -n "hello ={input.argument('name') ?: 'default value'}"
EOD
        );

        $this->assertEquals('hello world', $this->workspaceCommand('hello world')->getOutput());
    }

    /** @test */
    public function optionalOptionsCanBeSpecified()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('default_value'): 'default value'

command('hello [--name=<name>]'):
  env:
    NAME: "= input.option('name') ?: @('default_value')"
  exec: |
    #!bash
    echo -n "hello ${NAME}"
EOD
        );

        $this->assertEquals('hello world', $this->workspaceCommand('hello --name=world')->getOutput());
    }

    /** @test */
    public function optionalArgumentsCanBeLeftEmpty()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('default_value'): 'default value'

command('hello [<name>]'):
  exec: |
    #!bash|=
    echo -n "hello ={input.argument('name') ?: 'default value'}"
EOD
        );

        $this->assertEquals('hello default value', $this->workspaceCommand('hello')->getOutput());
    }

    /** @test */
    public function optionalOptionsCanBeLeftEmpty()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('default_value'): 'default value'

command('hello [--name=<name>]'):
  env:
    NAME: "= input.option('name') ?: @('default_value')"
  exec: |
    #!bash
    echo -n "hello ${NAME}"
EOD
        );

        $this->assertEquals('hello default value', $this->workspaceCommand('hello')->getOutput());
    }
}
