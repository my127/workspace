<?php

namespace Test\my127\Workspace\Environment;

use my127\Workspace\Tests\IntegrationTestCase;

class AttributesTest extends IntegrationTestCase
{
    /** @test */
    public function hostOsFamilyIsMadeAvailableAsAnAttribute(): void
    {
        $this->createWorkspaceYml(
            <<<'EOD'
command('get host os family'): |
  #!php
  echo $ws['host.os'];
EOD
        );

        $this->assertEquals(
            strtolower(PHP_OS_FAMILY),
            $this->workspaceCommand('get host os family')->getOutput()
        );
    }
}
