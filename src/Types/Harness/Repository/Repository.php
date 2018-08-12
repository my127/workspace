<?php

namespace my127\Workspace\Types\Harness\Repository;

use Exception;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use ReflectionProperty;

class Repository
{
    const HARNESS_PACKAGE_PATTERN = '/^((?P<vendor>[a-z0-9-]+)\/)?(?P<harness>[a-z0-9-]+){1}(:(?P<version>[a-z0-9-]+))?$/';
    const HARNESS_VERSION_PATTERN = '/^v(?<major>[0-9]+){1}(\.(?<minor>[0-9x]+))?(.(?<patch>[0-9x]+))?$/';

    private $packages = [];
    private $sources  = [];

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

    public function addPackage($name, $version, $dist)
    {
        $this->packages[$name][$version] = $dist;
    }

    public function addSource($url)
    {
        $this->sources[] = ['url' => $url, 'imported' => false];
    }

    public function get(string $package): Package
    {
        $this->importPackagesFromSources();

        preg_match(self::HARNESS_PACKAGE_PATTERN, $package, $match);

        $harness = $match['harness'];
        $vendor  = empty($match['vendor'])  ? 'my127'  : $match['vendor'];
        $version = $this->resolvePackageVersion($vendor.'/'.$harness, empty($match['version']) ? 'master' : $match['version']);

        return $this->hydrate([
            'name'    => $harness,
            'version' => $version,
            'dist'    => $this->packages[$vendor.'/'.$harness][$version]
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

    private function importPackagesFromSources()
    {
        foreach ($this->sources as $k => $source) {

            if ($source['imported']) {
                continue;
            }

            array_merge($this->packages, json_decode(file_get_contents($source['url'])));
            $this->sources[$k]['imported'] = true;
        }
    }

    private function resolvePackageVersion(string $name, string $version): string
    {
        if (isset($this->packages[$name][$version])) {
            return $version;
        }

        if (preg_match(self::HARNESS_VERSION_PATTERN, $version, $match)) {

            $collection = $this->packages[$name];

            $major = $match['major'];
            $minor = $match['minor']??'x';
            $patch = $match['patch']??'x';

            if (is_numeric($patch) && $minor == 'x') {
                throw new Exception("Invalid version string '{$version}'.");
            }

            $candidate = null;

            foreach ($collection as $version) {

                $semver = explode('.', substr($version, 1));

                if ($semver[0] != $major) {
                    continue;
                }

                if (is_numeric($minor) && $semver[1] != $minor) {
                    continue;
                }

                if (is_numeric($patch) && $semver[2] != $patch) {
                    continue;
                }

                if ($candidate == null || version_compare(substr($version, 1), substr($candidate, 1), '>')) {
                    $candidate = $version;
                }
            }

            if ($candidate !== null) {
                return $version;
            }
        }

        throw new Exception("Could not resolve '{$name}:{$version}' to a harness package'");
    }
}
