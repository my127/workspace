<?php

namespace my127\Workspace\Tests\Test\Application;

use my127\Console\Application\Executor;
use my127\Workspace\Tests\IntegrationTestCase;

class ApplicationTest extends IntegrationTestCase
{
    public function testExitsWithNonZeroExitCodeOnCommandNotFound(): void
    {
        self::assertEquals(
            Executor::EXIT_COMMAND_NOT_FOUND,
            $this->workspaceProcess('foobar')->run()
        );
    }

    public function testPrintsCommandNotFoundWhenInvokedWithArguments(): void
    {
        $process = $this->workspaceProcess('foobar');
        $process->run();
        self::assertStringContainsString('not recognised', $process->getOutput());
    }

    public function testDoesNotPrintCommandNotFoundWhenInvokedWithNoArguments(): void
    {
        $process = $this->workspaceProcess('');
        $process->run();
        self::assertStringNotContainsString('not recognised', $process->getOutput());
    }
}
