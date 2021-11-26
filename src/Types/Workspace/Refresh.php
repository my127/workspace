<?php

namespace my127\Workspace\Types\Workspace;

use my127\Workspace\Types\Confd\Factory as ConfdFactory;
use my127\Workspace\Types\Harness\Harness;

class Refresh
{
    private $workspace;
    private $confd;
    private $harness;

    public function __construct(Workspace $workspace, Harness $harness, ConfdFactory $confd)
    {
        $this->workspace = $workspace;
        $this->confd = $confd;
        $this->harness = $harness;
    }

    public function refresh(): void
    {
        $this->workspace->trigger('before.harness.refresh');

        if ($this->workspace->hasHarness()) {
            $this->applyConfiguration($this->harness->getRequiredConfdPaths());
        }

        $this->workspace->trigger('harness.refreshed');
        $this->workspace->trigger('after.harness.refresh');
    }

    private function applyConfiguration(array $paths): void
    {
        foreach ($paths as $path) {
            $this->confd->create($path)->apply();
        }
    }
}
