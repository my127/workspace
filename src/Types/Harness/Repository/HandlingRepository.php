<?php

declare(strict_types=1);

namespace my127\Workspace\Types\Harness\Repository;

interface HandlingRepository extends Repository
{
    public function handles(string $uri): bool;
}
