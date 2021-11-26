<?php

namespace my127\Workspace\Tests;

use my127\Workspace\Tests\Util\Workspace;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class IntegrationTestCase extends TestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }

    public function workspaceCommand(string $command, string $subPath = null, array $env = []): Process
    {
        $process = Process::fromShellCommandline(
            sprintf(__DIR__ . '/../my127ws.phar %s', $command),
            $this->workspace()->path($subPath),
            $env
        );
        $process->mustRun();

        return $process;
    }

    public function createWorkspaceYml(string $contents): void
    {
        $this->workspace()->put('workspace.yml', $contents);
    }

    public function removeAnsiColorEscapes(string $content)
    {
        return preg_replace('/\x1b\[[0-9;]*m/', '', $content);
    }
}
