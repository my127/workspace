<?php

namespace Test\my127\Workspace\Types;

use my127\Workspace\Tests\IntegrationTestCase;

class SubscriberTest extends IntegrationTestCase
{
    /** @test */
    public function subscriberScriptIsRunWhenAppropriateEventIsTriggered()
    {
        $this->createWorkspaceYml(<<<'EOD'
on('custom.event'): |
  #!bash
  echo -n "Hello World"

command('hi'): |
  #!php
  $ws->trigger('custom.event');
EOD
        );

        $this->assertEquals('Hello World', $this->workspaceCommand('hi')->getOutput());
    }

    /** @test */
    public function subscriberScriptIsRunWithEnvWhenTriggered()
    {
        $this->createWorkspaceYml(<<<'EOD'
on('custom.event'):
  env:
    EXAMPLE: test
  exec: |
    #!bash
    echo -n "Hello World, $EXAMPLE"

command('hi'): |
  #!php
  $ws->trigger('custom.event');
EOD
        );

        $this->assertEquals('Hello World, test', $this->workspaceCommand('hi')->getOutput());
    }

    /** @test */
    public function afterCanBeUsedAsAShorthandForEventNamesPrefixedWithAfter()
    {
        $this->createWorkspaceYml(<<<'EOD'
after('custom.event'): |
  #!bash
  echo -n "Hello World"

command('hi'): |
  #!php
  $ws->trigger('after.custom.event');
EOD
        );

        $this->assertEquals('Hello World', $this->workspaceCommand('hi')->getOutput());
    }

    /** @test */
    public function beforeCanBeUsedAsAShorthandForEventNamesPrefixedWithBefore()
    {
        $this->createWorkspaceYml(<<<'EOD'
before('custom.event'): |
  #!bash
  echo -n "Hello World"

command('hi'): |
  #!php
  $ws->trigger('before.custom.event');
EOD
        );

        $this->assertEquals('Hello World', $this->workspaceCommand('hi')->getOutput());
    }
}
