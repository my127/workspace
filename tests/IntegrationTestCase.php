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
        return Workspace::create(__DIR__ . '/Workspace', __DIR__ . '/WorkspaceHome');
    }

    public function workspaceProcess(string $command, string $subPath = null, array $env = []): Process
    {
        $env['MY127WS_HOME'] = isset($env['MY127WS_HOME']) ? $env['MY127WS_HOME'] : __DIR__ . '/WorkspaceHome';

        $process = Process::fromShellCommandline(
            sprintf(__DIR__ . '/../bin/workspace %s', $command),
            $this->workspace()->path($subPath),
            $env
        );

        return $process;
    }

    public function workspaceCommand(string $command, string $subPath = null, array $env = []): Process
    {
        $process = $this->workspaceProcess($command, $subPath, $env);
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
