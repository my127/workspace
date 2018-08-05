<?php

namespace my127\Workspace\Types\Workspace;

use ArrayAccess;
use my127\Workspace\Path\Path;
use my127\Workspace\Terminal\Terminal;
use my127\Workspace\Types\Attribute\Collection as AttributeCollection;
use my127\Workspace\Types\Confd\Confd;
use my127\Workspace\Types\Confd\Factory as ConfdFactory;
use my127\Workspace\Types\DynamicFunction\Collection as DynamicFunctionCollection;
use my127\Workspace\Types\Harness\Harness;
use my127\Workspace\Types\Harness\Repository\PackageRepository;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Workspace extends Definition implements ArrayAccess
{
    private $packages;
    private $confd;
    private $dispatcher;
    private $creator;
    private $functions;
    private $attributes;
    private $harness;
    private $terminal;
    private $pathResolver;

    public function __construct(
        Creator $creator,
        PackageRepository $packages,
        ConfdFactory $confd,
        EventDispatcher $dispatcher,
        DynamicFunctionCollection $functions,
        AttributeCollection $attributes,
        Harness $harness,
        Terminal $terminal,
        Path $pathResolver)
    {
        $this->packages     = $packages;
        $this->confd        = $confd;
        $this->dispatcher   = $dispatcher;
        $this->creator      = $creator;
        $this->functions    = $functions;
        $this->attributes   = $attributes;
        $this->harness      = $harness;
        $this->terminal     = $terminal;
        $this->pathResolver = $pathResolver;
    }

    public function hasHarness(): bool
    {
        return $this->harnessName !== null;
    }

    public function create(string $name, ?string $harness = null): void
    {
        $this->creator->create($name, $harness);
    }

    public function install($step = null): void
    {
        $installer = new Installer($this, $this->harness, $this->packages, $this->terminal, $this->attributes, $this->pathResolver, $this->confd);
        $installer->install($step);
    }

    public function run(string $command): void
    {
        preg_match_all ('/(?<=^|\s)([\'"]?)(.+?)(?<!\\\\)\1(?=$|\s)/', $command, $matches); // https://stackoverflow.com/a/34871367
        $argv = $matches[2];
        array_unshift($argv, 'ws');
        application()->run($argv);
    }

    public function exec(string $cmd)
    {
        passthru($cmd);
    }

    public function confd(string $directory): Confd
    {
        return $this->confd->create($directory);
    }

    public function trigger(string $event): void
    {
        $this->dispatcher->dispatch($event);
    }

    public function __invoke(string $command)
    {
        $this->run($command);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array($this->functions->get($name), $arguments);
    }

    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->attributes->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->attributes->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }
}
