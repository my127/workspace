<?php

namespace my127\Workspace\Tests\Unit\File;

use PHPUnit\Framework\TestCase;
use my127\Workspace\File\Exception\CouldNotLoadFile;
use my127\Workspace\File\FileLoader;
use my127\Workspace\Tests\IntegrationTestCase;

class FileLoaderTest extends IntegrationTestCase
{
    /** @test */
    public function test_it_loads_file(): void
    {
        $this->workspace()->put('test', 'foobar');
        self::assertEquals('foobar', $this->load($this->workspace()->path('test')));
    }

    /** @test */
    public function test_throws_an_exception_if_the_file_cannot_be_loaded(): void
    {
        $this->expectException(CouldNotLoadFile::class);
        self::assertEquals('foobar', $this->load($this->workspace()->path('test')));
    }

    private function load(string $url): string
    {
        return (new FileLoader())->load($url);
    }
}
