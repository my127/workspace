<?php

namespace my127\Workspace\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\Package\Package;

class AggregateRepository implements Repository
{
    /** @var HandlingRepository[] */
    private array $repositories;

    public function __construct(HandlingRepository ...$repositories)
    {
        $this->repositories = $repositories;
    }

    /**
     * @throws \Exception
     */
    public function get(string $package): Package
    {
        foreach ($this->repositories as $repository) {
            if ($repository->handles($package)) {
                return $repository->get($package);
            }
        }

        throw new \Exception(sprintf('No handler found for URI "%s"', $package));
    }
}
