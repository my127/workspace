<?php

namespace my127\Workspace\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\Package\Package;
use function str_starts_with;
use function substr;

class AggregateRepository implements Repository
{
    private Repository $packageRepository;

    private Repository  $archiveRepository;

    private Repository $localSyncRepository;

    public function __construct(
        Repository $packageRepository,
        Repository $archiveRepository,
        Repository $localSyncRepository
    ) {
        $this->packageRepository = $packageRepository;
        $this->archiveRepository = $archiveRepository;
        $this->localSyncRepository = $localSyncRepository;
    }

    public function get(string $package): Package
    {
        $parts = parse_url($package);
        if ($parts === false || empty($parts['scheme'])) {
            if (str_starts_with($package, 'sync:///')) {
                return $this->localSyncRepository->get(substr($package, 7));
            }
            if (str_contains($parts['path'], ':')) {
                return $this->packageRepository->get($package);
            }
        }

        return $this->archiveRepository->get($package);
    }
}
