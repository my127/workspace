<?php

namespace my127\Workspace\Tests\Util;

use PHPUnit\Framework\TestCase;

class WorkspaceTest extends TestCase
{
    public const EXAMPLE_CONTENT = 'barfoo';

    /**
     * @test
     */
    public function testResetWorkspace(): void
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
    public function testGetContents(): void
    {
        $workspace = Workspace::create(__DIR__ . '/../Workspace');
        $workspace->put('foobar', self::EXAMPLE_CONTENT);
        self::assertEquals(self::EXAMPLE_CONTENT, $workspace->getContents('foobar'));
    }

    /**
     * @test
     */
    public function testFileExists(): void
    {
        $workspace = Workspace::create(__DIR__ . '/../Workspace');
        $workspace->put('foobar', self::EXAMPLE_CONTENT);
        self::assertTrue($workspace->exists('foobar'));
        self::assertFalse($workspace->exists('barfoo'));
    }

    /**
     * @test
     */
    public function testProvidesFullPathToFile(): void
    {
        $workspace = Workspace::create(__DIR__ . '/../Workspace');
        $workspace->put('foobar', self::EXAMPLE_CONTENT);
        self::assertEquals(__DIR__ . '/../Workspace/foobar', $workspace->path('foobar'));
    }
}
