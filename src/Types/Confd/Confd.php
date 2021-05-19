<?php

namespace my127\Workspace\Types\Confd;

use my127\Workspace\Expression\Expression;
use my127\Workspace\Path\Path;
use Symfony\Component\Config\Definition\Exception\Exception;
use Twig_Environment;

class Confd
{
    private $definition;
    private $twig;
    private $path;
    private $expression;

    /** @var string */
    private $rootPath;

    public function __construct(Path $path, Definition $definition, Twig_Environment $twig, Expression $expression)
    {
        $this->definition = $definition;
        $this->twig       = $twig;
        $this->path       = $path;
        $this->expression = $expression;

        /** @var $loader Filesystem */
        $loader = $twig->getLoader();
        $this->rootPath = $loader->getRootPath();
    }

    public function apply(): void
    {
        foreach ($this->definition->getTemplates() as $path) {
            if (isset($path['with_items'])) {
                if (!is_iterable($path['with_items'])) {
                    throw new Exception("Expected with_items to be iterable");
                }

                foreach ($path['with_items'] as $key => $item) {
                    $this->applyPath($path, ['key' => $key, 'item' => $item]);
                }
            } else {
                $this->applyPath($path);
            }
        }
    }

    private function applyPath(string $path, array $values = []): void {
        if (isset($path['when']) && $this->expression->evaluate($path['when'], $values) === false) {
            return;
        }

        if (is_string($path)) {
            $src = $path.'.twig';
            $dst = $this->resolveDstFromSrc($src);
        } else {
            $src = $path['src'].'.twig';
            $dst = isset($path['dst']) ? $this->path->getRealPath($path['dst']) : $this->resolveDstFromSrc($src);
        }

        $dir = dirname($dst);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($dst, $this->twig->render($src, $values));
    }

    private function resolveDstFromSrc(string $path): string
    {
        return $this->rootPath.'/'.substr($path, 0, -5);
    }
}
