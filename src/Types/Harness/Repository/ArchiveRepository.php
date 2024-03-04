<?php

namespace my127\Workspace\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\Package\Package;

class ArchiveRepository implements HandlingRepository
{
    public function handles(string $uri): bool
    {
        return true;
    }

    public function get(string $package): Package
    {
        return new Package([
            'url' => $package,
        ]);
    }
}
