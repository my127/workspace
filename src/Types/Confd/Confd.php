<?php

namespace my127\Workspace\Types\Confd;

use my127\Workspace\Path\Path;
use Twig_Environment;

class Confd
{
    /** @var Definition */
    private $definition;

    /** @var Twig_Environment */
    private $twig;

    /** @var Path */
    private $path;

    public function __construct(Path $path, Definition $definition, Twig_Environment $twig)
    {
        $this->definition = $definition;
        $this->twig       = $twig;
        $this->path       = $path;
    }

    public function apply(): void
    {
        foreach ($this->definition->getTemplates() as $path) {

            $dst = $this->path->getRealPath($path['dst']);
            $dir = dirname($dst);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($dst, $this->twig->render($path['src']));
        }
    }
}
