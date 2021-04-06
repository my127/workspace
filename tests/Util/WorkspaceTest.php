<?php

namespace my127\Workspace\Tests\Util;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use my127\Workspace\Utility\Filesystem;

class WorkspaceTest extends TestCase
{
    const EXAMPLE_CONTENT = 'barfoo';

    /**
     * @test
     */
    public function test_reset_workspace(): void
    {
        $workspace = Workspace::create(__DIR__ . '/../Workspace');
        $workspace->put('foobar', self::EXAMPLE_CONTENT);
        $workspace->put('/barfoo/foobar', self::EXAMPLE_CONTENT);
        self::assertFileExists(__DIR__ . '/../Workspace/foobar');
        self::assertFileExists(__DIR__ . '/../Workspace/barfoo/foobar');
        $workspace->reset();
        self::assertFileDoesNotExist(__DIR__ . '/../Workspace/foobar');
        self::assertFileDoesNotExist(__DIR__ . '/../Workspace/barfoo/foobar');
    }

    /**
     * @test
     */
    public function test_get_contents(): void
    {
        $workspace = Workspace::create(__DIR__ . '/../Workspace');
        $workspace->put('foobar', self::EXAMPLE_CONTENT);
        self::assertEquals(self::EXAMPLE_CONTENT, $workspace->getContents('foobar'));
    }

    /**
     * @test
     */
    public function test_file_exists(): void
    {
        $workspace = Workspace::create(__DIR__ . '/../Workspace');
        $workspace->put('foobar', self::EXAMPLE_CONTENT);
        self::assertTrue($workspace->exists('foobar'));
        self::assertFalse($workspace->exists('barfoo'));
    }

    /**
     * @test
     */
    public function test_provides_full_path_to_file(): void
    {
        $workspace = Workspace::create(__DIR__ . '/../Workspace');
        $workspace->put('foobar', self::EXAMPLE_CONTENT);
        self::assertEquals(__DIR__ . '/../Workspace/foobar', $workspace->path('foobar'));
    }
}
