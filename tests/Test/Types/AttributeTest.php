<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use Generator;
use PHPUnit\Framework\TestCase;
use my127\Workspace\Tests\IntegrationTestCase;

class AttributeTest extends IntegrationTestCase
{
    /** @test */
    public function normal_attribute_key_can_be_set_and_retrieved()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): Hello World

command('speak'): |
  #!bash|@
  echo -n "@('message')"
EOD
        );

        $this->assertEquals("Hello World", $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function normal_attribute_root_object_can_be_set_and_retrieved()
    {
        $this->createWorkspaceYml(<<<'EOD'
attributes:
  my:
    message: Hello World

command('speak'): |
  #!bash|@
  echo -n "@('my.message')"
EOD
        );

        $this->assertEquals("Hello World", $this->workspaceCommand('speak')->getOutput());
    }


    /** @test */
    public function attribute_value_can_be_an_expression()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('db'):
  driver: mysql
  host: localhost
  name: application
  
attribute('db.dsn'): = @('db.driver') ~ ':host=' ~ @('db.host') ~ ';dbname=' ~ @('db.name')

command('speak'): |
  #!bash|@
  echo -n "@('db.dsn')"
EOD
        );

        $this->assertEquals("mysql:host=localhost;dbname=application", $this->workspaceCommand('speak')->getOutput());
    }

    /** @test */
    public function isset_returns_false_when_attribute_is_not_defined()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('isset'): |
  #!php
  echo (isset($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('no', $this->workspaceCommand('isset')->getOutput());
    }

    /** @test */
    public function isset_returns_true_when_attribute_is_defined_and_has_a_value()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): Hello World
command('isset'): |
  #!php
  echo (isset($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('yes', $this->workspaceCommand('isset')->getOutput());
    }

    /** @test */
    public function isset_returns_true_even_when_attribute_value_is_null()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): null
command('isset'): |
  #!php
  echo (isset($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('yes', $this->workspaceCommand('isset')->getOutput());
    }

    /** @test */
    public function null_values_are_also_represented_internally_as_null()
    {
        $this->createWorkspaceYml(<<<'EOD'
attribute('message'): null
command('isnull'): |
  #!php
  echo (is_null($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('yes', $this->workspaceCommand('isnull')->getOutput());
    }

    /** 
     * @test 
     * @dataProvider provide_attribute_precedence_is_respected
     */
    public function attribute_precedence_is_respected(string $attribute, string $expected)
    {
        $this->workspace()->loadSample('attribute/precedence');
        $this->assertEquals($expected, $this->workspaceCommand(sprintf(
            'get "%s"',
            $attribute
        ), 'workspace')->getOutput());
    }

    public function provide_attribute_precedence_is_respected(): Generator
    {
        yield ['key.1', 'Hello From harness.default'];
        yield ['key.2', 'Hello From harness.normal'];
        yield ['key.3', 'Hello From harness.override'];
        yield ['key.4', 'Hello From harness.override'];
        yield ['key.5', 'Hello From harness.override'];
        yield ['key.6', 'Hello From workspace.override'];
        yield ['key.7', 'Hello From workspace.override'];
        yield ['key.8', 'Hello From workspace.override'];
        yield ['key.9', 'Hello From global.override'];
    }
}
