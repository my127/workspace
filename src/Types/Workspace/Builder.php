<?php

namespace my127\Workspace\Types\Workspace;

use Exception;
use my127\Console\Application\Event\BeforeActionEvent;
use my127\Console\Application\Executor;
use my127\Console\Usage\Input;
use my127\Workspace\Application;
use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Environment\Builder as EnvironmentBuilder;
use my127\Workspace\Interpreter\Executors\PHP\Executor as PHPExecutor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Builder extends Workspace implements EnvironmentBuilder, EventSubscriberInterface
{
    /** @var Application */
    private $application;

    /** @var Workspace */
    private $workspace;

    /** @var PHPExecutor */
    private $phpExecutor;

    public function __construct(Application $application, Workspace $workspace, PHPExecutor $phpExecutor)
    {
        $this->application = $application;
        $this->workspace   = $workspace;
        $this->phpExecutor = $phpExecutor;
    }

    public function build(DefinitionCollection $definitions)
    {
        /** @var Definition $definition */
        if (($definition = $definitions->findOneByType(Definition::TYPE)) === null) {
            throw new Exception("No workspace declared");
        }

        $this->workspace->name        = $definition->name;
        $this->workspace->description = $definition->description;
        $this->workspace->path        = $definition->path;
        $this->workspace->harnessName = $definition->harnessName;
        $this->workspace->overlay     = $definition->overlay;
        $this->workspace->scope       = $definition->scope;

        $this->phpExecutor->setGlobal('ws', $this->workspace);

        if ($this->workspace->hasHarness()) {
            $this->application->section('install')
                ->usage('install [--from-step=<step>]')
                ->action(function(Input $input) {
                    $this->workspace->install($input->getOption('from-step'));
                });
        }
    }

    public function setInputGlobal(BeforeActionEvent $event)
    {
        $this->phpExecutor->setGlobal('input', $event->getInput());
    }

    public static function getSubscribedEvents()
    {
        return [Executor::EVENT_BEFORE_ACTION => 'setInputGlobal'];
    }
}
