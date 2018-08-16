<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    /** @test */
    public function normal_attribute_key_can_be_set_and_retrieved()
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
    public function normal_attribute_root_object_can_be_set_and_retrieved()
    {
        Fixture::workspace(<<<'EOD'
attributes:
  my:
    message: Hello World

command('speak'): |
  #!bash|@
  echo -n "@('my.message')"
EOD
        );

        $this->assertEquals("Hello World", run('speak'));
    }


    /** @test */
    public function attribute_value_can_be_an_expression()
    {
        Fixture::workspace(<<<'EOD'
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

        $this->assertEquals("mysql:host=localhost;dbname=application", run('speak'));
    }

    /** @test */
    public function isset_returns_false_when_attribute_is_not_defined()
    {
        Fixture::workspace(<<<'EOD'
command('isset'): |
  #!php
  echo (isset($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('no', run('isset'));
    }

    /** @test */
    public function isset_returns_true_when_attribute_is_defined_and_has_a_value()
    {
        Fixture::workspace(<<<'EOD'
attribute('message'): Hello World
command('isset'): |
  #!php
  echo (isset($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('yes', run('isset'));
    }

    /** @test */
    public function isset_returns_true_even_when_attribute_value_is_null()
    {
        Fixture::workspace(<<<'EOD'
attribute('message'): null
command('isset'): |
  #!php
  echo (isset($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('yes', run('isset'));
    }

    /** @test */
    public function null_values_are_also_represented_internally_as_null()
    {
        Fixture::workspace(<<<'EOD'
attribute('message'): null
command('isnull'): |
  #!php
  echo (is_null($ws['message'])) ? 'yes' : 'no';
EOD
        );

        $this->assertEquals('yes', run('isnull'));
    }

    /** @test */
    public function attribute_precedence_is_respected()
    {
        $path = Fixture::sampleData('attribute/precedence');

        chdir($path.'/workspace');

        $this->assertEquals('Hello From harness.default',  run('get "key.1"'));
        $this->assertEquals('Hello From harness.normal',   run('get "key.2"'));
        $this->assertEquals('Hello From harness.override', run('get "key.3"'));

        $this->assertEquals('Hello From harness.override',   run('get "key.4"'));
        $this->assertEquals('Hello From harness.override',   run('get "key.5"'));
        $this->assertEquals('Hello From workspace.override', run('get "key.6"'));

        $this->assertEquals('Hello From workspace.override', run('get "key.7"'));
        $this->assertEquals('Hello From workspace.override', run('get "key.8"'));
        $this->assertEquals('Hello From global.override',    run('get "key.9"'));
    }
}