<?php

namespace my127\Workspace\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\Package\Package;

class AggregateRepository implements Repository
{
    /** @var Repository */
    private $packageRepository;

    /** @var Repository */
    private $archiveRepository;

    public function __construct(Repository $packageRepository, Repository $archiveRepository)
    {
        $this->packageRepository = $packageRepository;
        $this->archiveRepository = $archiveRepository;
    }

    public function get(string $package): Package
    {
        $parts = parse_url($package);

        if (empty($parts['scheme']) || empty($parts['host']) || empty($parts['path'])) {
            return $this->packageRepository->get($package);
        }

        return $this->archiveRepository->get($package);
    }
}
