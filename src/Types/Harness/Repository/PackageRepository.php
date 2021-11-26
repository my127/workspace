<?php

namespace my127\Workspace\Types\Harness\Repository;

use Exception;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use ReflectionProperty;

class PackageRepository implements Repository
{
    public const HARNESS_PACKAGE_PATTERN = '/^((?P<vendor>[a-z0-9-]+)\/)?(?P<harness>[a-z0-9-]+){1}(:(?P<version>[a-z0-9.-]+))?$/';
    public const HARNESS_VERSION_PATTERN = '/^v(?<major>[0-9x]+){1}(\.(?<minor>[0-9x]+))?(.(?<patch>[0-9x]+))?$/';

    private $packages = [];
    private $sources = [];

    private $prototype;

    /** @var ReflectionProperty[] */
    private $properties = [];

    public function __construct()
    {
        $this->prototype = new Package();

        foreach (['name', 'version', 'dist'] as $name) {
            $this->properties[$name] = new ReflectionProperty(Package::class, $name);
            $this->properties[$name]->setAccessible(true);
        }
    }

    public function addPackage($name, $version, $dist): void
    {
        $this->packages[$name][$version] = $dist;
    }

    public function addSource($url): void
    {
        $this->sources[] = ['url' => $url, 'imported' => false];
    }

    public function get(string $package): Package
    {
        $this->importPackagesFromSources();

        preg_match(self::HARNESS_PACKAGE_PATTERN, $package, $match);

        $harness = $match['harness'];
        $vendor = empty($match['vendor']) ? 'my127' : $match['vendor'];
        $version = $this->resolvePackageVersion($vendor.'/'.$harness, empty($match['version']) ? 'vx.x.x' : $match['version']);

        return $this->hydrate([
            'name' => $harness,
            'version' => $version,
            'dist' => $this->packages[$vendor.'/'.$harness][$version],
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

    private function importPackagesFromSources(): void
    {
        foreach ($this->sources as $k => $source) {
            if ($source['imported']) {
                continue;
            }

            $this->packages = array_merge($this->packages, json_decode(file_get_contents($source['url']), true));
            $this->sources[$k]['imported'] = true;
        }
    }

    private function resolvePackageVersion(string $name, string $version): string
    {
        if (isset($this->packages[$name][$version])) {
            return $version;
        }

        if (preg_match(self::HARNESS_VERSION_PATTERN, $version, $match)) {
            $collection = array_keys($this->packages[$name]);

            $major = $match['major'] ?? 'x';
            $minor = $match['minor'] ?? 'x';
            $patch = $match['patch'] ?? 'x';

            $candidate = null;

            foreach ($collection as $availVersion) {
                $semver = explode('.', substr($availVersion, 1));

                if (is_numeric($major) && $semver[0] != $major) {
                    continue;
                }

                if (is_numeric($minor) && $semver[1] != $minor) {
                    continue;
                }

                if (is_numeric($patch) && $semver[2] != $patch) {
                    continue;
                }

                if (null == $candidate || version_compare(substr($availVersion, 1), substr($candidate, 1), '>')) {
                    $candidate = $availVersion;
                }
            }

            if (null !== $candidate) {
                return $availVersion;
            }
        }

        throw new Exception("Could not resolve '{$name}:{$version}' to a harness package'");
    }
}
