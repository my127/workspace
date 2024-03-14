<?php

declare(strict_types=1);

namespace my127\Workspace\Types\Harness\Repository;

use Exception;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use function preg_match;

class GithubRepository implements HandlingRepository
{
    // format: github:git@github.com:inviqa/harness-base-php.git:0.4.x
    const PATTERN = '#^github:(?<urn>.+):(?<ref>[^:]+)$#';
    const KEY_REF = 'ref';
    const KEY_URN = 'urn';

    public function handles(string $uri): bool
    {
        return (bool)preg_match(self::PATTERN, $uri);
    }

    public function get(string $package): Package
    {
        if (!preg_match(self::PATTERN, $package, $matches)) {
            throw new Exception("Package '$package' not matching git URL pattern.");
        }

        return new Package([
            'url' => $matches[self::KEY_URN],
            'ref' => $matches[self::KEY_REF],
            'git' => true,
        ]);
    }
}