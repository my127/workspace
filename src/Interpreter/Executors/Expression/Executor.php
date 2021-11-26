<?php

namespace my127\Workspace\Interpreter\Executors\Expression;

use my127\Workspace\Expression\Expression;
use my127\Workspace\Interpreter\Executor as InterpreterExecutor;

class Executor implements InterpreterExecutor
{
    public const NAME = 'expr';

    /** @var Expression */
    private $expression;

    public function __construct(Expression $expression)
    {
        $this->expression = $expression;
    }

    public function exec(string $script, array $args = [], string $cwd = null, array $env = []): void
    {
        $this->expression->evaluate($this->getExpressionFromScript($script), $args);
    }

    public function capture(string $script, array $args = [], string $cwd = null, array $env = []): string
    {
        return $this->expression->evaluate($this->getExpressionFromScript($script), $args);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    private function getExpressionFromScript(string $script): string
    {
        return preg_replace('/^.+\n/', '', $script);
    }
}
