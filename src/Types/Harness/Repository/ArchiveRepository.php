<?php

namespace my127\Workspace\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\Package\Package;

class ArchiveRepository implements Repository
{
    public function get(string $package): Package
    {
        return new Package([
            'url' => $package,
        ]);
    }
}
