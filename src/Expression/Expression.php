<?php

namespace my127\Workspace\Expression;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as SymfonyExpressionLanguage;

class Expression extends SymfonyExpressionLanguage
{
    public function __construct(CacheItemPoolInterface $cache = null, $providers = array())
    {
        parent::__construct($cache, $providers);

        $this->addDefaultFunctions();
    }

    public function evaluate($expression, $values = array())
    {
        return parent::evaluate($this->preProcessExpression($expression), $values);
    }

    private function preProcessExpression(string $expression): string
    {
        return str_replace('@(', 'attr(', $expression); // hack so we can use '@' as a shorthand function call for attributes
    }

    private function addDefaultFunctions()
    {
        $this->addFunction(ExpressionFunction::fromPhp('getenv',   'env'));
        $this->addFunction(ExpressionFunction::fromPhp('var_dump', 'debug'));
    }
}
