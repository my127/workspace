<?php

namespace my127\Workspace\Interpreter\Executors\PHP;

use my127\Workspace\Interpreter\Executor as InterpreterExecutor;

class Executor implements InterpreterExecutor
{
    public const NAME = 'php';

    /** @var array */
    private $environment;

    /** @var array */
    private $globals;

    public function exec(string $script, array $args = [], string $cwd = null, array $env = []): void
    {
        $this->run($script, $args, $cwd, $env);
    }

    public function capture(string $script, array $args = [], string $cwd = null, array $env = [])
    {
        $pos = strrpos($script, "\n") + 1;

        if (false !== $pos && '=' == $script[$pos]) {
            $script = substr_replace($script, 'return ', $pos, 1);
        }

        return $this->run($script, $args, $cwd, $env);
    }

    public function setGlobal(string $name, $value)
    {
        $this->globals[$name] = $value;
    }

    private function run(string $script, array $args = [], string $cwd = null, array $env = [])
    {
        $this->environment['cwd'] = getcwd();
        $this->environment['env'] = [];

        foreach ($env as $key => $value) {
            $this->environment['env'][$key] = getenv($key);
            putenv($key.'='.$value);
        }

        if (null !== $cwd) {
            chdir($cwd ?? getcwd());
        }

        extract($this->globals);
        extract($args);

        $ret = eval($script);

        chdir($this->environment['cwd']);

        foreach ($this->environment['env'] as $key => $value) {
            putenv($key.(false !== $value) ?: '='.$value);
        }

        return $ret;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
