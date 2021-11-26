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
        $process = proc_open($this->buildCommand($script, $args, $cwd, $env), $descriptorSpec, $pipes);

        $status = 255;

        if (is_resource($process)) {
            $status = proc_close($process);
        }

        if (0 !== $status) {
            exit($status);
        }
    }

    public function capture(string $script, array $args = [], string $cwd = null, array $env = []): string
    {
        $pos = strrpos($script, "\n") + 1;

        if ('=' == $script[$pos]) {
            $script = substr_replace($script, 'echo -n ', $pos, 1);
        }

        exec($this->buildCommand($script, $args, $cwd, $env), $output, $status);

        if (0 !== $status) {
            exit($status);
        }

        return implode("\n", $output);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    private function buildCommand(string $script, array $args, ?string $cwd, array $env): string
    {
        $home = home();
        $header = "#!/bin/bash\n"
                 .". {$home}/.my127/workspace/lib/sidekick.sh\n";

        foreach ($args as $key => $value) {
            $header .= $key.'="'.addslashes($value).'"'."\n";
        }

        foreach ($env as $key => $value) {
            $header .= 'export '.$key.'="'.addslashes($value).'"'."\n";
        }

        $header .= 'cd '.$cwd ?? getcwd();

        return 'bash -e -c '.escapeshellarg(substr_replace($script, $header, 0, strpos($script, "\n")));
    }
}
