<?php

namespace my127\Workspace\Interpreter\Executors\Bash;

use my127\Workspace\Interpreter\Executor as InterpreterExecutor;
use Symfony\Component\Process\Process;

class Executor implements InterpreterExecutor
{
    public const NAME = 'bash';

    public function exec(string $script, array $args = [], string $cwd = null, array $env = []): void
    {
        $process = $this->getScriptProcess($script, $args, $cwd, $env);
        $process->setTimeout(3600);
        $process->run(function ($type, $buffer) { echo $buffer; });
    }

    public function capture(string $script, array $args = [], string $cwd = null, array $env = []): string
    {
        $pos = strrpos($script, "\n") + 1;

        if ($script[$pos] == '=') {
            $script = substr_replace($script, 'echo -n ', $pos, 1);
        }

        $process = $this->getScriptProcess($script, $args, $cwd, $env);
        $process->setTimeout(3600);
        $process->run();

        return $process->getOutput();
    }

    public function getName(): string
    {
        return self::NAME;
    }

    private function getScriptProcess(string $script, array $args, ?string $cwd, array $env): Process
    {
        return new Process($this->buildCommand($script, $args), $cwd??getcwd(), $env);
    }

    private function buildCommand(string $script, array $args = []): string
    {
        $home   = home();
        $header = "#!/bin/bash\n"
                 .". {$home}/.my127/workspace/lib/sidekick.sh\n";

        foreach ($args as $key => $value) {
            $header .= $key.'=\"'.addslashes($value).'\"'."\n";
        }

        $script = escapeshellarg(preg_replace('/^.+\n/', $header, $script));
        $cmd    = 'echo '.$script.' | bash';

        return $cmd;
    }
}
