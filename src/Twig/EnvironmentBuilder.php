<?php

namespace my127\Workspace\Twig;

use my127\Workspace\Path\Path;
use my127\Workspace\Twig\Loader\Filesystem;
use Twig_Environment;
use Twig_Function;

class EnvironmentBuilder
{
    /** @var Path */
    private $path;

    /** @var Twig_Function[] */
    private $functions = [];

    /** @var mixed[] */
    private $globals = [];

    public function __construct(Path $path)
    {
        $this->path = $path;
    }

    public function addFunction(string $name, callable $function): void
    {
        $this->functions[$name] = new Twig_Function($name, $function);
    }

    public function addGlobal(string $name, $value)
    {
        $this->globals[$name] = $value;
    }

    public function create(string $path): Twig_Environment
    {
        $directory = $this->path->getRealPath($path);

        $loader      = new Filesystem([$directory], $directory);
        $environment = new Twig_Environment($loader, [
            'autoescape' => false
        ]);

        foreach ($this->functions as $function) {
            $environment->addFunction($function);
        }

        foreach ($this->globals as $name => $value) {
            $environment->addGlobal($name, $value);
        }


        return $environment;
    }
}
