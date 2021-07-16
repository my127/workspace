<?php

namespace my127\Workspace\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use my127\Workspace\Tests\Util\Workspace;

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
            sprintf(__DIR__ . '/../ws.phar %s', $command),
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
}
