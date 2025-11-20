<?php

namespace my127\Workspace\Twig\Loader;

use Twig\Loader\FilesystemLoader;
use Twig\Source;

class Filesystem extends FilesystemLoader
{
    private $path;

    public function __construct($paths = [], ?string $rootPath = null)
    {
        parent::__construct($paths, $rootPath);

        $this->path = $rootPath;
    }

    public function getSourceContext($name): Source
    {
        $path = $this->findTemplate($name);
        $content = file_get_contents($path);

        $content = str_replace("@('", "attr('", $content);
        $content = str_replace('@("', 'attr("', $content);

        return new Source($content, $name, $path);
    }

    public function getRootPath(): string
    {
        return $this->path;
    }
}
