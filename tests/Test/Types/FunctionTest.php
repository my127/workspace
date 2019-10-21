<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use PHPUnit\Framework\TestCase;

class FunctionTest extends TestCase
{
    /** @test */
    public function bash_can_be_used_as_an_interpreter_for_a_function()
    {
        Fixture::workspace(<<<'EOD'
function('add', [v1, v2]): |
  #!bash
  ="$((v1+v2))"

command('add <v1> <v2>'): |
  #!php
  echo $ws->add($input->getArgument('v1'), $input->getArgument('v2'));
EOD
        );

        $this->assertEquals("4", run('add 2 2'));
    }

    /** @test */
    public function php_can_be_used_as_an_interpreter_for_a_function()
    {
        Fixture::workspace(<<<'EOD'
function('add', [v1, v2]): |
  #!php
  =$v1+$v2;

command('add <v1> <v2>'): |
  #!php
  echo $ws->add($input->getArgument('v1'), $input->getArgument('v2'));
EOD
        );

        $this->assertEquals("4", run('add 2 2'));
    }

    /** @test */
    public function bash_function_can_make_use_of_environment_variables()
    {
        Fixture::workspace(<<<'EOD'
function('hello', [v1]):
  env:
    MESSAGE: Hello
  exec: |
    #!bash
    ="${MESSAGE} ${v1}"

command('hi'): |
  #!bash|=
  echo -n "={ hello('World') }"
EOD
        );

        $this->assertEquals("Hello World", run('hi'));
    }

    /** @test */
    public function php_function_can_make_use_of_environment_variables()
    {
        Fixture::workspace(<<<'EOD'
function('hello', [v1]):
  env:
    MESSAGE: Hello
  exec: |
    #!php
    =getenv('MESSAGE').' '.$v1;

command('hi'): |
  #!bash|=
  echo -n "={ hello('World') }"
EOD
        );

        $this->assertEquals("Hello World", run('hi'));
    }

    /** @test */
    public function functions_are_available_within_attribute_expressions()
    {
        Fixture::workspace(<<<'EOD'
attribute('answer'): = add(2, 2)

function('add', [v1, v2]): |
  #!php
  =$v1+$v2;
  
command('hi'): |
  #!bash|@
  echo -n "@('answer')"
  
EOD
        );

        $this->assertEquals("4", run('hi'));
    }

    /** @test */
    public function functions_are_able_to_return_non_scalar_types()
    {
        Fixture::workspace(<<<'EOD'
function('array', [v1, v2]): |
  #!php
  = [$v1, $v2];

command('array <v1> <v2>'): |
  #!php
  echo json_encode($ws->array($input->getArgument('v1'), $input->getArgument('v2')));
EOD
        );

        $this->assertEquals('["2","2"]', run('array 2 2'));
    }
}
