<?php

namespace my127\Workspace\Types\Subscriber;

use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Environment\Builder as EnvironmentBuilder;
use my127\Workspace\Environment\Environment;
use my127\Workspace\Expression\Expression;
use my127\Workspace\Interpreter\Interpreter;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Builder implements EnvironmentBuilder
{
    /** @var EventDispatcher */
    private $dispatcher;

    /** @var Expression */
    private $expression;

    /** @var Interpreter */
    private $interpreter;

    public function __construct(EventDispatcher $dispatcher, Expression $expression, Interpreter $interpreter)
    {
        $this->dispatcher = $dispatcher;
        $this->expression = $expression;
        $this->interpreter = $interpreter;
    }

    public function build(Environment $environment, DefinitionCollection $definitions)
    {
        foreach (DefinitionFactory::getTypes() as $type) {
            foreach ($definitions->findByType($type) as $definition) {
                /* @var Definition $definition */
                $this->dispatcher->addListener($definition->getEvent(), new Subscriber($definition, $this->expression, $this->interpreter));
            }
        }
    }
}
