<?php

namespace my127\Workspace\Twig\Loader;

use Twig_Loader_Filesystem;
use Twig_Source;

class Filesystem extends Twig_Loader_Filesystem
{
    public function getSourceContext($name)
    {
        $path    = $this->findTemplate($name);
        $content = file_get_contents($path);

        $content = str_replace("@('", "attr('", $content);
        $content = str_replace('@("', 'attr("', $content);

        return new Twig_Source($content, $name, $path);
    }
}
