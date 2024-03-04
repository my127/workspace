<?php

declare(strict_types=1);

namespace my127\Workspace\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\Package\Package;
use my127\Workspace\Types\Harness\Repository\Repository;

class LocalSyncRepository implements Repository
{
    public function get(string $package): Package
    {
        return new Package([
            'url' => rtrim($package, '/') . '/',
            'localsync' => true,
        ]);
    }
}