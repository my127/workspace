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

    public function testGlobalServicesList(): void
    {
        $this->workspace();
        $process = $this->workspaceProcess('global service');
        $process->run();
        self::assertStringContainsString("proxy\n", $process->getOutput());
    }

    public function testGlobalServicesExist(): void
    {
        $this->workspace();
        $initScript = $_SERVER['MY127WS_HOME'] . '/.my127/workspace/service/test/init.sh';
        mkdir(dirname($initScript), 0755, true);
        file_put_contents($initScript, '#!/bin/bash
set -e

DIR=""

main()
{
    if [ "$1" = "enable" ]; then
        echo "enabling"
        exit
    fi
    exit 1
}

main "$1"
');
        chmod($initScript, 0755);
        $process = $this->workspaceProcess('global service test enable');
        $process->run();
        self::assertEquals("enabling\n", $process->getOutput());
    }
}
