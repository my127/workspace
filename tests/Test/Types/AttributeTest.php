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
workspace('phpunit'): ~

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
workspace('phpunit'): ~

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
workspace('phpunit'): ~

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
    public function attribute_precidence_is_respected()
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