<?php

namespace my127\Workspace\Types\Command;

use my127\Workspace\Expression\Expression;
use my127\Workspace\Interpreter\Interpreter;

class Command
{
    /** @var Definition */
    private $definition;

    /** @var Interpreter */
    private $interpreter;

    /** @var Expression */
    private $expression;

    public function __construct(Definition $definition, Expression $expression, Interpreter $interpreter)
    {
        $this->definition = $definition;
        $this->interpreter = $interpreter;
        $this->expression = $expression;
    }

    public function __invoke()
    {
        $env = $this->evaluateEnvironmentVariables($this->definition->getEnvironmentVariables());
        $script = $this->definition->getExec();

        try {
            $this->interpreter->script($script)->exec(null, $env);
        } catch (\Throwable $e) {
            throw new \Exception(sprintf('Command "%s" failed due to "%s" on line %d', $this->definition->getSection(), $e->getMessage(), $e->getLine()), 0, $e);
        }
    }

    private function evaluateEnvironmentVariables(array $env): array
    {
        foreach ($env as $key => $value) {
            if ($this->isExpression($value)) {
                $env[$key] = $this->expression->evaluate(substr($value, 1));
            }
        }

        return $env;
    }

    private function isExpression(string $value): bool
    {
        return is_string($value) && ($value[0] == '=');
    }
}
