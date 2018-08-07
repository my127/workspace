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

    public function __construct(Path $path, CacheItemPoolInterface $cache = null, $providers = array())
    {
        parent::__construct($cache, $providers);

        $this->path = $path;
        $this->addDefaultFunctions();
    }

    public function evaluate($expression, $values = array())
    {
        return parent::evaluate($this->preProcessExpression($expression), array_merge($this->globals, $values));
    }

    public function setGlobal($name, $value)
    {
        $this->globals[$name] = $value;
    }

    private function preProcessExpression(string $expression): string
    {
        return str_replace('@(', 'attr(', $expression); // hack so we can use '@' as a shorthand function call for attributes
    }

    private function addDefaultFunctions()
    {
        $this->addFunction(ExpressionFunction::fromPhp('getenv',            'env'));
        $this->addFunction(ExpressionFunction::fromPhp('var_dump',          'debug'));
        $this->addFunction(ExpressionFunction::fromPhp('file_get_contents', 'file'));

        $this->register(
            'file',
            function()
            {
                throw new Exception('cannot be compiled');
            },
            function ($args, $file)
            {
                return file_get_contents($this->path->getRealPath($file));
            }
        );
    }
}
