<?php

namespace my127\Workspace\Tests\Test\Application;

use PHPUnit\Framework\TestCase;
use my127\Console\Application\Executor;
use my127\Workspace\Tests\IntegrationTestCase;

class ApplicationTest extends IntegrationTestCase
{
    public function testExitsWithNonZeroExitCodeOnCommandNotFound(): void
    {
        self::assertEquals(
            Executor::EXIT_COMMAND_NOT_FOUND,
            $this->workspaceCommand('foobar')->run()
        );
    }
}
