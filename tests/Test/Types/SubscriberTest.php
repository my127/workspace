<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use PHPUnit\Framework\TestCase;

class SubscriberTest extends TestCase
{
    /** @test */
    public function subscriber_script_is_run_when_appropriate_event_is_triggered()
    {
        Fixture::workspace(<<<'EOD'
workspace('phpunit'): ~

on('custom.event'): |
  #!bash
  echo -n "Hello World"

command('hi'): |
  #!php
  $ws->trigger('custom.event');
EOD
        );

        $this->assertEquals("Hello World", run('hi'));
    }

    /** @test */
    public function after_can_be_used_as_a_shorthand_for_event_names_prefixed_with_after()
    {
        Fixture::workspace(<<<'EOD'
workspace('phpunit'): ~

after('custom.event'): |
  #!bash
  echo -n "Hello World"

command('hi'): |
  #!php
  $ws->trigger('after.custom.event');
EOD
        );

        $this->assertEquals("Hello World", run('hi'));
    }

    /** @test */
    public function before_can_be_used_as_a_shorthand_for_event_names_prefixed_with_before()
    {
        Fixture::workspace(<<<'EOD'
workspace('phpunit'): ~

before('custom.event'): |
  #!bash
  echo -n "Hello World"

command('hi'): |
  #!php
  $ws->trigger('before.custom.event');
EOD
        );

        $this->assertEquals("Hello World", run('hi'));
    }
}
