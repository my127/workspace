<?php

declare(strict_types=1);

namespace my127\Workspace\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\Package\Package;

class LocalSyncRepository implements HandlingRepository
{
    public function handles(string $uri): bool
    {
        return \str_starts_with($uri, 'sync://');
    }

    public function get(string $package): Package
    {
        return new Package([
            'url' => rtrim(\substr($package, 6), '/') . '/',
            'localsync' => true,
        ]);
    }
}
