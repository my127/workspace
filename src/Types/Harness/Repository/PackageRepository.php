<?php

namespace my127\Workspace\Types\Harness\Repository;

use Exception;
use RuntimeException;
use my127\Workspace\File\JsonLoader;
use my127\Workspace\Types\Harness\Repository\Exception\CouldNotLoadSource;
use my127\Workspace\Types\Harness\Repository\Exception\UnknownPackage;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use ReflectionProperty;

class PackageRepository implements Repository
{
    const HARNESS_PACKAGE_PATTERN = '/^((?P<vendor>[a-z0-9-]+)\/)?(?P<harness>[a-z0-9-]+){1}(:(?P<version>[a-z0-9.-]+))?$/';
    const HARNESS_VERSION_PATTERN = '/^v(?<major>[0-9x]+){1}(\.(?<minor>[0-9x]+))?(.(?<patch>[0-9x]+))?$/';

    private $packages = [];
    private $sources  = [];

    private $prototype;

    /** @var ReflectionProperty[] */
    private $properties = [];

    /**
     * @var JsonLoader
     */
    private $fileLoader;

    public function __construct(JsonLoader $fileLoader)
    {
        $this->prototype = new Package();

        foreach (['name', 'version', 'dist'] as $name) {
            $this->properties[$name] = new ReflectionProperty(Package::class, $name);
            $this->properties[$name]->setAccessible(true);
        }
        $this->fileLoader = $fileLoader;
    }

    public function addPackage(string $name, string $version, array $dist)
    {
        $this->parseVersionString($version);
        $this->packages[$name][$version] = $dist;
    }

    public function addSource($url)/** void **/
    {
        $this->sources[] = ['url' => $url, 'imported' => false];
    }

    public function get(string $package): Package
    {
        $this->importPackagesFromSources();

        preg_match(self::HARNESS_PACKAGE_PATTERN, $package, $match);

        if (!isset($match['harness'])) {
            throw new RuntimeException(sprintf(
                'Package name "%s" is invalid',
                $package
            ));
        }

        $harness = $match['harness'];
        $vendor  = empty($match['vendor'])  ? 'my127'  : $match['vendor'];
        $version = $this->resolvePackageVersion($vendor.'/'.$harness, empty($match['version']) ? 'vx.x.x' : $match['version']);

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

            try {
                $this->packages = array_merge($this->packages, $this->fileLoader->loadArray($source['url']));
            } catch (Exception $error) {
                throw new CouldNotLoadSource(sprintf(
                    'Could not load from source "%s"',
                    $source['url']
                ), 0, $error);
            }
            $this->sources[$k]['imported'] = true;
        }
    }

    private function resolvePackageVersion(string $name, string $version): string
    {
        [$major, $minor, $patch] = $this->parseVersionString($version);

        if (isset($this->packages[$name][$version])) {
            return $version;
        }

        if (!isset($this->packages[$name])) {
            throw new UnknownPackage(sprintf(
                'Package "%s" is not registered, registered packages "%s"',
                $name, implode('", "', array_keys($this->packages))
            ));
        }

        $availableVersions = array_keys($this->packages[$name]);

        $candidate = null;

        foreach ($availableVersions as $availableVersion) {

            $semver = explode('.', substr($availableVersion, 1));

            if (is_numeric($major) && $semver[0] != $major) {
                continue;
            }

            if (is_numeric($minor) && $semver[1] != $minor) {
                continue;
            }

            if (is_numeric($patch) && $semver[2] != $patch) {
                continue;
            }

            if ($candidate == null || version_compare(substr($availableVersion, 1), substr($candidate, 1), '>')) {
                $candidate = $availableVersion;
            }
        }

        if ($candidate === null) {
            throw new Exception(sprintf('Could not resolve "%s:%s" to a harness package', $name, $version));
        }

        return $candidate;
    }

    /**
     * @return array{int,int,int}
     */
    private function parseVersionString(string $version): array
    {
        if (preg_match(self::HARNESS_VERSION_PATTERN, $version, $match)) {
            return [
                $match['major']??'x',
                $match['minor']??'x',
                $match['patch']??'x',
            ];
        }

        throw new RuntimeException(sprintf('Invalid version string "%s"', $version));

    }
}
