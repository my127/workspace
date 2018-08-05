<?php

namespace Test\my127\Workspace\Environment;

use Fixture;
use PHPUnit\Framework\TestCase;

class AttributesTest extends TestCase
{
    /** @test */
    public function host_os_family_is_made_available_as_an_attribute()
    {
        Fixture::workspace(<<<'EOD'
command('get host os family'): |
  #!php
  echo $ws['host.os'];
EOD
        );

        $this->assertEquals(strtolower(PHP_OS_FAMILY), run('get host os family'));
    }
}
