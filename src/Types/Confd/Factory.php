<?php

namespace my127\Workspace\Types\Confd;

use my127\Workspace\Expression\Expression;
use my127\Workspace\Path\Path;
use my127\Workspace\Twig\EnvironmentBuilder as TwigEnvironment;

class Factory
{
    private $definitions;
    private $twig;
    private $path;
    private $expression;

    public function __construct(Path $path, Collection $definitions, TwigEnvironment $twig, Expression $expression)
    {
        $this->definitions = $definitions;
        $this->twig = $twig;
        $this->path = $path;
        $this->expression = $expression;
    }

    public function create(string $directory): Confd
    {
        $confd = new Confd(
            $this->path,
            $this->definitions->get($directory),
            $this->twig->create($directory),
            $this->expression
        );

        return $confd;
    }
}
