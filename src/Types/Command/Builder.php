<?php

namespace my127\Workspace\Types\Command;

use my127\Workspace\Application;
use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Environment\Builder   as EnvironmentBuilder;
use my127\Workspace\Expression\Expression;
use my127\Workspace\Interpreter\Interpreter;

class Builder implements EnvironmentBuilder
{
    /** @var Application */
    private $application;

    /** @var Interpreter */
    private $interpreter;

    /** @var Expression */
    private $expression;

    public function __construct(Expression $expression, Application $application, Interpreter $interpreter)
    {
        $this->application = $application;
        $this->interpreter = $interpreter;
        $this->expression  = $expression;
    }

    public function build(DefinitionCollection $definitions)
    {
        /** @var Definition $definition */
        foreach ($definitions->findByType(Definition::TYPE) as $definition) {
            $this->application->section($definition->getSection())
                ->usage($definition->getUsage())
                ->action(new Command($definition, $this->expression, $this->interpreter));
        }
    }
}
