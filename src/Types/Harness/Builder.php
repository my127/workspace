<?php

namespace my127\Workspace\Types\Harness;

use my127\Workspace\Application;
use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Environment\Builder as EnvironmentBuilder;
use my127\Workspace\Environment\Environment;
use my127\Workspace\Interpreter\Executors\PHP\Executor as PHPExecutor;

class Builder extends Harness implements EnvironmentBuilder
{
    /** @var Application */
    private $application;

    /** @var Harness */
    private $harness;

    /** @var PHPExecutor */
    private $phpExecutor;

    public function __construct(Application $application, Harness $harness, PHPExecutor $phpExecutor)
    {
        $this->application = $application;
        $this->harness = $harness;
        $this->phpExecutor = $phpExecutor;
    }

    public function build(Environment $environment, DefinitionCollection $definitions)
    {
        if (($definition = $definitions->findOneByType(Definition::TYPE)) === null) {
            return;
        }

        assert($definition instanceof Definition);
        $this->harness->name = $definition->name;
        $this->harness->description = $definition->description;
        $this->harness->path = $definition->path;
        $this->harness->require = $definition->require;
        $this->harness->scope = $definition->scope;

        $this->phpExecutor->setGlobal('harness', $this->harness);
    }
}
