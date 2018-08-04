<?php

namespace Test\my127\Workspace\Types;

use Fixture;
use PHPUnit\Framework\TestCase;

class ConfdTest extends TestCase
{
    /** @test */
    public function attributes_are_available_to_templates()
    {
        $path = Fixture::sampleData('confd/attributes');
        run('apply config');

        $this->assertEquals('Hello World', file_get_contents($path.'/test.txt'));
    }

    /** @test */
    public function functions_are_available_to_templates()
    {
        $path = Fixture::sampleData('confd/functions');
        run('apply config');

        $this->assertEquals('6', file_get_contents($path.'/test.txt'));
    }
}
