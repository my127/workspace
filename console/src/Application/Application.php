<?php

namespace my127\Console\Application;

use my127\Console\Application\Action\ActionCollection;
use my127\Console\Application\Plugin\Plugin;
use my127\Console\Application\Section\Section;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Application
{
    /** @var string */
    private $version;

    /** @var Executor */
    private $executor;

    /** @var EventDispatcher */
    private $dispatcher;

    /** @var Section */
    private $root;

    /** @var Plugin[] */
    private $plugins = [];

    /** @var ActionCollection */
    private $registeredActions;

    public function __construct(string $name, string $description = '', string $version = '', Executor $executor, EventDispatcher $dispatcher)
    {
        $this->version           = $version;
        $this->executor          = $executor;
        $this->dispatcher        = $dispatcher;
        $this->registeredActions = $executor->getActionCollection();

        $this->root = (new Section($name))
            ->description($description);
    }

    public function getExecutor(): Executor
    {
        return $this->executor;
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->dispatcher;
    }

    public function getActionCollection(): ActionCollection
    {
        return $this->executor->getActionCollection();
    }

    public function register(callable $action, string $name = ''): Application
    {
        $this->registeredActions->add($action, $name);
        return $this;
    }

    public function description(string $description): Application
    {
        $this->root->setDescription($description);
        return $this;
    }

    public function usage(string $usage): Application
    {
        $this->root->addUsageDefinition($usage);
        return $this;
    }

    public function option(string $option): Application
    {
        $this->root->addOption($option);
        return $this;
    }

    public function action($action): Application
    {
        $this->root->setAction($action);
        return $this;
    }

    public function plugin(Plugin $plugin): Application
    {
        $this->plugins[] = $plugin;
        $plugin->setup($this);
        return $this;
    }

    public function on(string $event, callable $listener): Application
    {
        $this->dispatcher->addListener($event, $listener);
        return $this;
    }

    public function section($name): Section
    {
        return $this->root->get($this->root->getName().' '.$name);
    }

    public function run(?array $argv = null): void
    {
        if ($argv === null) {
            global $argv;
            $argv[0] = $this->root->getName();
        }

        $this->executor->run($this->root, $argv);
    }

    public function getRootSection(): Section
    {
        return $this->root;
    }
}
