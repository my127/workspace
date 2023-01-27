<?php

namespace Test\my127\Workspace\Environment;

use my127\Workspace\Tests\IntegrationTestCase;

class AttributesTest extends IntegrationTestCase
{
    /** @test */
    public function hostOsFamilyIsMadeAvailableAsAnAttribute()
    {
        $this->createWorkspaceYml(<<<'EOD'
command('get host os family'): |
  #!php
  echo $ws['host.os'];
EOD
        );

        $process = $this->workspaceCommand('get host os family');
        $process->mustRun();
        $this->assertEquals(
            strtolower(PHP_OS_FAMILY),
            $process->getOutput()
        );
    }
}
