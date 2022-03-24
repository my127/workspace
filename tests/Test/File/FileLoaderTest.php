<?php

namespace my127\Workspace\Tests\Unit\File;

use my127\Workspace\File\Exception\CouldNotLoadFile;
use my127\Workspace\File\FileLoader;
use my127\Workspace\Tests\IntegrationTestCase;

class FileLoaderTest extends IntegrationTestCase
{
    /** @test */
    public function testItLoadsFile(): void
    {
        $this->workspace()->put('test', 'foobar');
        self::assertEquals('foobar', $this->load($this->workspace()->path('test')));
    }

    /** @test */
    public function testThrowsAnExceptionIfTheFileCannotBeLoaded(): void
    {
        $this->expectException(CouldNotLoadFile::class);
        self::assertEquals('foobar', $this->load($this->workspace()->path('test')));
    }

    private function load(string $url): string
    {
        return (new FileLoader())->load($url);
    }
}
