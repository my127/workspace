<?php

namespace my127\Workspace\Expression;

use my127\Workspace\Interpreter\Filter as InterpreterFilter;

class Filter implements InterpreterFilter
{
    const NAME = '=';

    private $expression;

    public function __construct(Expression $expression)
    {
        $this->expression = $expression;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function apply(string $script): string
    {
        foreach ($this->findAllExpressionsInString($script) as $expression) {

            $script = str_replace(
                $expression,
                $this->expression->evaluate(trim(substr($expression, 2, -1))),
                $script
            );
        }


        return $script;
    }

    private function findAllExpressionsInString(string $script): array
    {
        $expressions = [];

        while (($end = $start = strpos($script, '={', $end??0)) !== false) {

            while ($script[($end = strpos($script, '}', $end))-1] == '\\') {
                ++$end;
            }

            $expressions[] = substr($script, $start, (($end + 1) - $start));
        }

        return $expressions;
    }
}
