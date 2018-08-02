<?php

namespace my127\Workspace\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\Package;
use ReflectionProperty;

class PackageRepository
{
    const HARNESS_PACKAGE_PATTERN = '/^((?P<vendor>[a-z0-9-]+)\/)?(?P<harness>[a-z0-9-]+){1}(:(?P<version>[a-z0-9-]+))?$/';

    private $sources = [];

    /** @var Package */
    private $prototype;

    /** @var ReflectionProperty[] */
    private $properties = [];

    public function __construct()
    {
        $this->prototype = new Package();

        foreach (['vendor', 'name', 'version', 'url', 'type'] as $name) {
            $this->properties[$name] = new ReflectionProperty(Package::class, $name);
            $this->properties[$name]->setAccessible(true);
        }
    }

    public function addSource(string $name, array $packages)
    {
        $this->sources[$name] = $packages;
    }

    public function get(string $name): Package
    {
        preg_match(self::HARNESS_PACKAGE_PATTERN, $name, $match);

        $harness = $match['harness'];
        $vendor  = empty($match['vendor'])  ? '~'      : $match['vendor'];
        $version = empty($match['version']) ? 'latest' : $match['version'];
        $dist    = $this->sources[$vendor][$harness][$version];

        return $this->hydrate([
            'vendor'  => $vendor,
            'name'    => $harness,
            'version' => $version,
            'url'     => $dist['url'],
            'type'    => $dist['type']
        ]);
    }

    private function hydrate(array $values): Package
    {
        $package = clone $this->prototype;

        foreach ($this->properties as $name => $property) {
            $property->setValue($package, $values[$name]);
        }

        return $package;
    }
}
