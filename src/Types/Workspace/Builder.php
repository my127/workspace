<?php

namespace my127\Workspace\Types\Workspace;

use my127\Console\Application\Event\BeforeActionEvent;
use my127\Console\Application\Executor;
use my127\Console\Usage\Input;
use my127\Console\Usage\Model\OptionValue;
use my127\Workspace\Application;
use my127\Workspace\Definition\Collection as DefinitionCollection;
use my127\Workspace\Definition\Definition as WorkspaceDefinition;
use my127\Workspace\Environment\Builder as EnvironmentBuilder;
use my127\Workspace\Environment\Environment;
use my127\Workspace\Expression\Expression;
use my127\Workspace\Interpreter\Executors\PHP\Executor as PHPExecutor;
use my127\Workspace\Types\Attribute\Builder as AttributeBuilder;
use my127\Workspace\Types\Attribute\Collection as AttributeCollection;
use my127\Workspace\Types\Confd\Definition as ConfdDefinition;
use my127\Workspace\Types\Harness\Definition as HarnessDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Builder extends Workspace implements EnvironmentBuilder, EventSubscriberInterface
{
    private $application;
    private $workspace;
    private $phpExecutor;
    private $attributes;
    private $expression;

    public function __construct(Application $application, Workspace $workspace, PHPExecutor $phpExecutor, AttributeCollection $attributes, Expression $expression)
    {
        $this->application = $application;
        $this->workspace = $workspace;
        $this->phpExecutor = $phpExecutor;
        $this->attributes = $attributes;
        $this->expression = $expression;
    }

    public function build(Environment $environment, DefinitionCollection $definitions): void
    {
        if (($definition = $definitions->findOneByType(Definition::TYPE)) !== null) {
            /* @var Definition $definition */
            $this->workspace->name = $definition->name;
            $this->workspace->description = $definition->description;
            $this->workspace->path = $definition->path;
            $this->workspace->harnessName = $definition->harnessName;
            $this->workspace->overlay = $definition->overlay;
            $this->workspace->scope = $definition->scope;
        } else {
            $this->workspace->name = basename($environment->getWorkspacePath());
            $this->workspace->description = '';
            $this->workspace->path = $environment->getWorkspacePath();
            $this->workspace->harnessName = null;
            $this->workspace->overlay = null;
            $this->workspace->scope = WorkspaceDefinition::SCOPE_WORKSPACE;
        }

        $this->phpExecutor->setGlobal('ws', $this->workspace);

        $this->attributes->add(
            [
                'workspace' => [
                    'name' => $this->workspace->name,
                    'description' => $this->workspace->description,
                    'harness' => $this->workspace->harnessName,
                ],
                'namespace' => $this->workspace->name,
            ],
            AttributeBuilder::PRECEDENCE_WORKSPACE_DEFAULT
        );

        $this->expression->register('exec', function (): void {
        }, function ($args, $cmd) {
            return $this->workspace->exec($cmd);
        });

        $this->expression->register('passthru', function (): void {
        }, function ($args, $cmd): void {
            $this->workspace->passthru($cmd);
        });

        if ($definitions->hasType(ConfdDefinition::TYPE) || $definitions->hasType(HarnessDefinition::TYPE)) {
            $this->application->section('refresh')
                ->usage('refresh')
                ->action(function (): void {
                    $this->workspace->refresh();
                });
        }

        if ($this->workspace->hasHarness()) {
            $this->application->section('install')
                ->usage('install')
                ->option('--step=<step>   Step from which to start installer. [default: 1]')
                ->option('--skip-events   If set events will not be triggered.')
                ->action(function (Input $input): void {
                    $this->workspace->install($input);
                });

            $this->application->section('harness download')
                ->usage('harness download')
                ->action(function (Input $input): void {
                    $this->workspace->run('install --step=download');
                });

            $this->application->section('harness prepare')
                ->usage('harness prepare')
                ->action(function (Input $input): void {
                    $this->workspace->run('install --step=overlay');
                    $this->workspace->run('install --step=prepare');
                });

            $this->application->section('config dump')
                ->option('--key=<key>   Attribute key to dump.')
                ->usage('config dump --key=<key>')
                ->action(function (Input $input) use ($environment): void {
                    $key = $input->getOption('key');
                    $key = $key instanceof OptionValue ? $key->value() : $key;
                    $attribute = $environment->getAttribute($key);

                    if (null === $attribute) {
                        echo sprintf("Attribute with key %s not found\n", $key);

                        return;
                    }
                    var_dump($attribute);
                });
        }
    }

    public function setInputGlobal(BeforeActionEvent $event): void
    {
        $this->expression->setGlobal('input', $event->getInput());
        $this->phpExecutor->setGlobal('input', $event->getInput());
    }

    public static function getSubscribedEvents()
    {
        return [Executor::EVENT_BEFORE_ACTION => 'setInputGlobal'];
    }
}
