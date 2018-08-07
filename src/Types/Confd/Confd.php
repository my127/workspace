<?php

namespace my127\Workspace\Types\Confd;

use my127\Workspace\Path\Path;
use my127\Workspace\Twig\Loader\Filesystem;
use Twig_Environment;

class Confd
{
    /** @var Definition */
    private $definition;

    /** @var Twig_Environment */
    private $twig;

    /** @var Path */
    private $path;

    /** @var string */
    private $rootPath;

    public function __construct(Path $path, Definition $definition, Twig_Environment $twig)
    {
        $this->definition = $definition;
        $this->twig       = $twig;
        $this->path       = $path;

        /** @var $loader Filesystem */
        $loader = $twig->getLoader();

        $this->rootPath = $loader->getRootPath();
    }

    public function apply(): void
    {
        foreach ($this->definition->getTemplates() as $path) {

            if (is_string($path)) {
                $src = $path.'.twig';
                $dst = $this->resolveDstFromSrc($path);
            } else {
                $src = $path['src'];
                $dst = $this->path->getRealPath($path['dst']);
            }

            $dir = dirname($dst);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($dst, $this->twig->render($src));
        }
    }

    private function resolveDstFromSrc(string $path): string
    {
        return $this->rootPath.'/'.$path;
    }
}
