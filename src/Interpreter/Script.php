<?php

namespace my127\Workspace\Interpreter;

class Script
{
    /** @var Executor */
    private $executor;

    /** @var string */
    private $script;

    /** @var string */
    private $cwd;

    /** @var array */
    private $arguments;

    public function __construct(Executor $executor, string $cwd, string $script, array $arguments = [])
    {
        $this->executor = $executor;
        $this->script = $script;
        $this->cwd = $cwd;
        $this->arguments = $arguments;
    }

    public function exec(?array $args = [], ?array $env = []): void
    {
        $this->executor->exec($this->script, $this->buildArgList($args), $this->cwd, $env);
    }

    public function capture(?array $args = [], ?array $env = [])
    {
        return $this->executor->capture($this->script, $this->buildArgList($args), $this->cwd, $env);
    }

    private function buildArgList(?array $args): array
    {
        if (null === $args) {
            return [];
        }

        return array_combine($this->arguments, array_pad($args, count($this->arguments), null));
    }
}
