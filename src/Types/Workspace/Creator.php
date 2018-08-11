<?php

namespace my127\Workspace\Types\Workspace;

use Exception;
use my127\Workspace\Types\Crypt\Key;

class Creator
{
    public function create(string $name, ?string $harness = null)
    {
        $dir = './'.$name;

        if (is_dir('./'.$name)) {
            throw new Exception("directory '{$dir}' already exists.");
        }

        mkdir($dir);

        $workspace = [];
        $workspace[] = "";
        $workspace[] = "workspace('{$name}'):";
        $workspace[] = "  description: generated local workspace for {$name}.";

        if (null !== $harness) {
            $workspace[] = "  harness: $harness";
        }

        $workspace[] = "";

        $override = [];
        $override[] = "key('default'): ".(new Key('default'))->getKeyAsHex();
        $override[] = "";

        file_put_contents($dir.'/workspace.yml', implode("\n", $workspace));
        file_put_contents($dir.'/workspace.override.yml', implode("\n", $override));
    }
}
