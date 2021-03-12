<?php

namespace Test\my127\Workspace\Environment;

use Fixture;
use PHPUnit\Framework\TestCase;
use my127\Workspace\Tests\IntegrationTestCase;

class AttributesTest extends IntegrationTestCase
{
    /** @test */
    public function host_os_family_is_made_available_as_an_attribute()
    {
        $this->workspace()->put('workspace.yml', <<<'EOD'
command('get host os family'): |
  #!php
  echo $ws['host.os'];
EOD
        );

        $this->assertEquals(
            strtolower(PHP_OS_FAMILY),
            $this->ws('get host os family')->getOutput()
        );
    }
}
