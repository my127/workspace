<?php

namespace my127\Workspace\Tests\Test\Application;

use my127\Console\Application\Executor;
use my127\Workspace\Tests\IntegrationTestCase;

class ApplicationTest extends IntegrationTestCase
{
    public function testExitsWithZeroExitCodeOnSuccess(): void
    {
        self::assertEquals(
            Executor::EXIT_OK,
            $this->workspaceProcess('version')->run()
        );
    }

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
        self::assertStringContainsString('not recognised', $process->getErrorOutput());
    }

    public function testDoesNotPrintCommandNotFoundWhenInvokedWithNoArguments(): void
    {
        $process = $this->workspaceProcess('');
        $process->run();
        self::assertStringNotContainsString('not recognised', $process->getErrorOutput());
    }

    public function testExitsWithZeroExitCodeWithNoCommand(): void
    {
        self::assertEquals(
            Executor::EXIT_OK,
            $this->workspaceProcess('')->run()
        );
    }

    public function testExitsWithZeroExitCodeWithLongHelpOption(): void
    {
        self::assertEquals(
            Executor::EXIT_OK,
            $this->workspaceProcess('--help')->run()
        );
    }

    public function testExitsWithZeroExitCodeWithShortHelpOption(): void
    {
        self::assertEquals(
            Executor::EXIT_OK,
            $this->workspaceProcess('-h')->run()
        );
    }

    public function testExitsWithZeroExitCodeWithLongHelpOptionOnValidCommand(): void
    {
        self::assertEquals(
            Executor::EXIT_OK,
            $this->workspaceProcess('--help version')->run()
        );
    }

    public function testExitsWithZeroExitCodeWithShortHelpOptionOnValidCommand(): void
    {
        self::assertEquals(
            Executor::EXIT_OK,
            $this->workspaceProcess('-h version')->run()
        );
    }

    public function testExitsWithCommandNotFoundExitCodeWithLongHelpOptionOnInvalidCommand(): void
    {
        self::assertEquals(
            Executor::EXIT_COMMAND_NOT_FOUND,
            $this->workspaceProcess('--help idonotexist')->run()
        );
    }

    public function testExitsWithCommandNotFoundExitCodeWithShortHelpOptionOnInvalidCommand(): void
    {
        self::assertEquals(
            Executor::EXIT_COMMAND_NOT_FOUND,
            $this->workspaceProcess('-h idonotexist')->run()
        );
    }

    public function testInstallsHomeDirectoryContent(): void
    {
        $currentTime = microtime(true);
        $buildFile = __DIR__ . '/../../../home/build';
        $homeFile = $_SERVER['MY127WS_HOME'] . '/.my127/workspace/build';
        file_put_contents($buildFile, $currentTime);
        $this->workspace();
        $this->workspaceProcess('')->run();
        self::assertFileExists($homeFile);
        self::assertFileEquals($buildFile, $homeFile);
    }
}
