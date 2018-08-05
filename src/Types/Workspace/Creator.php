<?php

namespace my127\Workspace\Types\Workspace;

class Creator
{
    public function create(string $name, ?string $harness = null)
    {
        $template = [];
        $template[] = "";
        $template[] = "workspace('{$name}'):";
        $template[] = "  description: generated local workspace for {$name}.";

        if (null !== $harness) {
            $template[] = "  harness: $harness";
        }

        $template[] = "";

        file_put_contents('workspace.yml', implode("\n", $template));
    }
}
