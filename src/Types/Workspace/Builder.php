<?php

namespace my127\Workspace\Types\Workspace;

use my127\Console\Application\Event\BeforeActionEvent;
use my127\Console\Application\Executor;
use my127\Console\Usage\Input;
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
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var PHPExecutor
     */
    private $phpExecutor;

    /**
     * @var AttributeCollection
     */
    private $attributes;

    /**
     * @var Expression
     */
    private $expression;

    public function __construct(Application $application, Workspace $workspace, PHPExecutor $phpExecutor, AttributeCollection $attributes, Expression $expression)
    {
        $this->application = $application;
        $this->workspace = $workspace;
        $this->phpExecutor = $phpExecutor;
        $this->attributes = $attributes;
        $this->expression = $expression;
    }

    public function build(Environment $environment, DefinitionCollection $definitions)
    {
        // ignore PHPStan errors below. It does not currently understand that
        // this class extends Definition because of that we can access the protected
        // properties of the definitions...
        if (($definition = $definitions->findOneByType(Definition::TYPE)) !== null) {
            /* @phpstan-ignore-next-line */
            $this->workspace->name = $definition->name;
            /* @phpstan-ignore-next-line */
            $this->workspace->description = $definition->description;
            /* @phpstan-ignore-next-line */
            $this->workspace->path = $definition->path;
            /* @phpstan-ignore-next-line */
            $this->workspace->harnessLayers = $definition->harnessLayers;
            /* @phpstan-ignore-next-line */
            $this->workspace->overlay = $definition->overlay;
            /* @phpstan-ignore-next-line */
            $this->workspace->scope = $definition->scope;
            /* @phpstan-ignore-next-line */
            $this->workspace->require = $definition->require;
        } else {
            $this->workspace->name = basename($environment->getWorkspacePath());
            $this->workspace->description = '';
            $this->workspace->path = $environment->getWorkspacePath();
            $this->workspace->harnessLayers = [];
            $this->workspace->overlay = null;
            $this->workspace->scope = WorkspaceDefinition::SCOPE_WORKSPACE;
        }

        $this->phpExecutor->setGlobal('ws', $this->workspace);

        $this->attributes->add(
            [
                'workspace' => [
                    'name' => $this->workspace->name,
                    'description' => $this->workspace->description,
                    'harnessLayers' => $this->workspace->harnessLayers,
                ],
                'namespace' => $this->workspace->name,
            ],
            'src/Types/Workspace/Builder.php',
            AttributeBuilder::PRECEDENCE_WORKSPACE_DEFAULT
        );

        $this->expression->register('exec', function () {}, function ($args, $cmd) {
            return $this->workspace->exec($cmd);
        });

        $this->expression->register('passthru', function () {}, function ($args, $cmd) {
            $this->workspace->passthru($cmd);
        });

        if ($definitions->hasType(ConfdDefinition::TYPE) || $definitions->hasType(HarnessDefinition::TYPE)) {
            $this->application->section('refresh')
                ->usage('refresh')
                ->action(function () {
                    $this->workspace->refresh();
                });
        }

        if ($this->workspace->hasHarness()) {
            $this->application->section('install')
                ->usage('install')
                ->option('--step=<step>   Step from which to start installer. [default: 1]')
                ->option('--skip-events   If set events will not be triggered.')
                ->action(function (Input $input) {
                    $this->workspace->install($input);
                });

            $this->application->section('harness download')
                ->usage('harness download')
                ->action(function (Input $input) {
                    $this->workspace->run('install --step=download');
                });

            $this->application->section('harness prepare')
                ->usage('harness prepare')
                ->action(function (Input $input) {
                    $this->workspace->run('install --step=overlay');
                    $this->workspace->run('install --step=prepare');
                });
        }

        $this->application->section('config dump')
            ->option('--key=<key>   Attribute key to dump.')
            ->usage('config dump --key=<key>')
            ->action(function (Input $input) use ($environment) {
                $key = $input->getOption('key');
                $key = $key->value();
                $attribute = $environment->getAttribute($key);
                if ($attribute === null) {
                    echo sprintf("Attribute with key %s not found\n", $key);

                    return;
                }
                var_dump($attribute);
                echo "specified in:\n";
                array_map(
                    function ($a) { echo $a['source'] . "\n"; }, $environment->getAttributeMetadata($key)
                );
            });
    }

    public function setInputGlobal(BeforeActionEvent $event)
    {
        $this->expression->setGlobal('input', $event->getInput());
        $this->phpExecutor->setGlobal('input', $event->getInput());
    }

    public static function getSubscribedEvents()
    {
        return [Executor::EVENT_BEFORE_ACTION => 'setInputGlobal'];
    }
}
