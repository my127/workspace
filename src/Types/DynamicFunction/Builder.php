<?php

namespace my127\Workspace\Types\DynamicFunction;

use Exception;
use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Environment\Builder as EnvironmentBuilder;
use my127\Workspace\Environment\Environment;
use my127\Workspace\Expression\Expression;
use my127\Workspace\Interpreter\Interpreter;
use my127\Workspace\Twig\EnvironmentBuilder as TwigEnvironmentBuilder;

class Builder implements EnvironmentBuilder
{
    /** @var Collection */
    private $collection;

    /** @var Interpreter */
    private $interpreter;

    /** @var TwigEnvironmentBuilder */
    private $twig;

    /** @var Expression */
    private $expression;

    public function __construct(Collection $collection, Interpreter $interpreter, TwigEnvironmentBuilder $twig, Expression $expression)
    {
        $this->collection = $collection;
        $this->interpreter = $interpreter;
        $this->twig = $twig;
        $this->expression = $expression;
    }

    public function build(Environment $environment, DefinitionCollection $definitions)
    {
        foreach ($definitions->findByType(Definition::TYPE) as $definition) {
            /* @var Definition $definition */
            $this->collection->add(new DynamicFunction($this->interpreter, $definition));
        }

        foreach ($this->collection as $function) {
            /* @var DynamicFunction $function */

            $this->twig->addFunction($function->getName(), $function);

            $this->expression->register(
                $function->getName(),
                function () use ($function) {
                    throw new Exception("Compilation of the '{$function->getName()}' function within Types\DynamicFunction\Builder is not supported.");
                },
                function ($arguments, ...$args) use ($function) {
                    return call_user_func_array($function, $args);
                }
            );
        }
    }
}
