<?php

namespace my127\Workspace\Expression;

use Exception;
use my127\Workspace\Path\Path;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as SymfonyExpressionLanguage;

class Expression extends SymfonyExpressionLanguage
{
    private $globals = [];
    /**
     * @var Path
     */
    private $path;

    public function __construct(Path $path, CacheItemPoolInterface $cache = null, $providers = [])
    {
        parent::__construct($cache, $providers);

        $this->path = $path;
        $this->addDefaultFunctions();
    }

    public function evaluate($expression, $values = [])
    {
        return parent::evaluate($this->preProcessExpression($expression), array_merge($this->globals, $values));
    }

    public function setGlobal($name, $value): void
    {
        $this->globals[$name] = $value;
    }

    private function preProcessExpression(string $expression): string
    {
        return str_replace('@(', 'attr(', $expression); // hack so we can use '@' as a shorthand function call for attributes
    }

    private function addDefaultFunctions(): void
    {
        $this->addFunction(ExpressionFunction::fromPhp('getenv', 'env'));
        $this->addFunction(ExpressionFunction::fromPhp('var_dump', 'debug'));
        $this->addFunction(ExpressionFunction::fromPhp('file_get_contents', 'file'));
        $this->addFunction(ExpressionFunction::fromPhp('join', 'join'));
        $this->addFunction(ExpressionFunction::fromPhp('max', 'max'));
        $this->addFunction(ExpressionFunction::fromPhp('min', 'min'));
        $this->addFunction(ExpressionFunction::fromPhp('range', 'range'));
        $this->addFunction(ExpressionFunction::fromPhp('explode', 'split'));

        $this->register(
            'file',
            function (): void {
                throw new Exception('cannot be compiled');
            },
            function ($args, $file) {
                return file_get_contents($this->path->getRealPath($file));
            }
        );
    }
}
