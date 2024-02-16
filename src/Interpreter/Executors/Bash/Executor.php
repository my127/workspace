<?php

namespace my127\Workspace\Interpreter\Executors\Bash;

use my127\Workspace\Interpreter\Executor as InterpreterExecutor;

class Executor implements InterpreterExecutor
{
    public const NAME = 'bash';

    public function exec(string $script, array $args = [], string $cwd = null, array $env = []): void
    {
        $descriptorSpec = [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        ];

        $pipes = [];
        $currentEnv = $this->buildEnv() ?: [];
        $process = proc_open($this->buildCommand($script, $args, $cwd), $descriptorSpec, $pipes, null, array_merge($currentEnv, $env));

        $status = 255;
        if (is_resource($process)) {
            $status = proc_close($process);
        }

        if ($status !== 0) {
            exit($status);
        }
    }

    public function capture(string $script, array $args = [], string $cwd = null, array $env = []): string
    {
        $pos = strrpos($script, "\n") + 1;

        if ($script[$pos] == '=') {
            $script = substr_replace($script, 'echo -n ', $pos, 1);
        }

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => STDERR,
        ];

        $pipes = [];
        $currentEnv = $this->buildEnv() ?: [];
        $process = proc_open($this->buildCommand($script, $args, $cwd), $descriptorSpec, $pipes, null, array_merge($currentEnv, $env));

        $output = '';
        $status = 255;
        if (is_resource($process)) {
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $status = proc_close($process);
        }

        if ($status !== 0) {
            exit($status);
        }

        return $output;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param string[] $args arguments to the command
     *
     * @return string[]
     **/
    private function buildCommand(string $script, array $args, ?string $cwd): array
    {
        $home = home();
        $header = "#!/bin/bash\n"
                 . ". {$home}/.my127/workspace/lib/sidekick.sh\n";

        foreach (array_keys($args) as $index => $key) {
            $header .= $key . '=$' . ($index + 1) . "\n";
        }

        $header .= 'cd ' . ($cwd ?? getcwd());

        return [
            'bash',
            '-e',
            '-c',
            substr_replace($script, $header, 0, strpos($script, "\n")),
            '--',
            ...array_values($args),
        ];
    }

    /**
     * @return array<string,string>
     */
    private function buildEnv(): array
    {
        return getenv();
    }
}
