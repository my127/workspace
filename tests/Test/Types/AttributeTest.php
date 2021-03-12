<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use PHPUnit\Framework\TestCase;
use my127\Workspace\Tests\IntegrationTestCase;

class AttributeTest extends IntegrationTestCase
{
    /** @test */
    public function normal_attribute_key_can_be_set_and_retrieved()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
attribute('message'): Hello World

command('speak'): |
  #!bash|@
  echo -n "@('message')"
EOD
        );

        $this->assertEquals("Hello World", $this->ws('speak')->getOutput());
    }

    /** @test */
    public function normal_attribute_root_object_can_be_set_and_retrieved()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
attributes:
  my:
    message: Hello World

command('speak'): |
  #!bash|@
  echo -n "@('my.message')"
EOD
        );

        $this->assertEquals("Hello World", $this->ws('speak')->getOutput());
    }


    /** @test */
    public function attribute_value_can_be_an_expression()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
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

        $this->assertEquals("mysql:host=localhost;dbname=application", $this->ws('speak')->getOutput());
    }

    /** @test */
    public function isset_returns_false_when_attribute_is_not_defined()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
command('isset'): |
  #!php
  echo (isset($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('no', $this->ws('isset')->getOutput());
    }

    /** @test */
    public function isset_returns_true_when_attribute_is_defined_and_has_a_value()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
attribute('message'): Hello World
command('isset'): |
  #!php
  echo (isset($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('yes', $this->ws('isset')->getOutput());
    }

    /** @test */
    public function isset_returns_true_even_when_attribute_value_is_null()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
attribute('message'): null
command('isset'): |
  #!php
  echo (isset($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('yes', $this->ws('isset')->getOutput());
    }

    /** @test */
    public function null_values_are_also_represented_internally_as_null()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
attribute('message'): null
command('isnull'): |
  #!php
  echo (is_null($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('yes', $this->ws('isnull')->getOutput());
    }

    /** @test */
    public function attribute_precedence_is_respected()
    {
        $this->workspace()->loadSample('attribute/precedence');

        $this->assertEquals('Hello From harness.default',  $this->ws('get "key.1"', 'workspace')->getOutput());
        $this->assertEquals('Hello From harness.normal',   $this->ws('get "key.2"', 'workspace')->getOutput());
        $this->assertEquals('Hello From harness.override', $this->ws('get "key.3"', 'workspace')->getOutput());

        $this->assertEquals('Hello From harness.override',   $this->ws('get "key.4"', 'workspace')->getOutput());
        $this->assertEquals('Hello From harness.override',   $this->ws('get "key.5"', 'workspace')->getOutput());
        $this->assertEquals('Hello From workspace.override', $this->ws('get "key.6"', 'workspace')->getOutput());

        $this->assertEquals('Hello From workspace.override', $this->ws('get "key.7"', 'workspace')->getOutput());
        $this->assertEquals('Hello From workspace.override', $this->ws('get "key.8"', 'workspace')->getOutput());
        $this->assertEquals('Hello From global.override',    $this->ws('get "key.9"', 'workspace')->getOutput());
    }
}
