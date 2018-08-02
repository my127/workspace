<?php

namespace my127\Workspace\Types\Confd;

use my127\Workspace\Path\Path;
use my127\Workspace\Twig\EnvironmentBuilder as TwigEnvironment;

class Factory
{
    /** @var Collection */
    private $definitions;

    /** @var TwigEnvironment */
    private $twig;

    /** @var Path */
    private $path;

    public function __construct(Path $path, Collection $definitions, TwigEnvironment $twig)
    {
        $this->definitions = $definitions;
        $this->twig        = $twig;
        $this->path        = $path;
    }

    public function create(string $directory): Confd
    {
        $confd = new Confd(
            $this->path,
            $this->definitions->get($directory),
            $this->twig->create($directory)
        );

        return $confd;
    }
}
