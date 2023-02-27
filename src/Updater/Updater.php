<?php

namespace my127\Workspace\Updater;

use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use my127\Workspace\Updater\Exception\NoUpdateAvailableException;
use my127\Workspace\Updater\Exception\NoVersionDeterminedException;

class Updater
{
    public const CODE_ERR_FETCHING_RELEASES = 100;
    public const CODE_NO_RELEASES = 101;
    public const CODE_ERR_FETCHING_NEXT_RELEASE = 102;

    public const STABILITY_STABLE = 0;
    public const STABILITY_RC = 5;
    public const STABILITY_BETA = 10;
    public const STABILITY_ALPHA = 15;
    public const STABILITY_DEV = 20;

    /** @var array<string, self::STABILITY_*> */
    public static $stabilities = [
        'stable' => self::STABILITY_STABLE,
        'RC' => self::STABILITY_RC,
        'beta' => self::STABILITY_BETA,
        'alpha' => self::STABILITY_ALPHA,
        'dev' => self::STABILITY_DEV,
    ];

    /** @var string */
    private $apiUrl;

    /** @var Output */
    private $output;

    public function __construct(string $apiUrl, ?Output $output = null)
    {
        $this->apiUrl = $apiUrl;
        $this->output = $output ?: new StdOutput();
    }

    public function updateLatest(string $currentVersion, string $targetPath)
    {
        $latest = $this->getLatestRelease();
        if (!$latest->isMoreRecentThan($currentVersion)) {
            throw new NoUpdateAvailableException($currentVersion);
        }
        $this->doUpdate($currentVersion, $latest, $targetPath);
    }

    public function update(string $currentVersion, string $targetConstraint, string $targetPath)
    {
        $latest = $this->getLatestReleaseByConstraint($targetConstraint);
        if ($latest->getVersion() == $currentVersion) {
            throw new NoUpdateAvailableException($currentVersion);
        }
        $this->doUpdate($currentVersion, $latest, $targetPath);
    }

    private function doUpdate(string $currentVersion, Release $release, string $targetPath)
    {
        if (empty($currentVersion)) {
            throw new NoVersionDeterminedException();
        }

        $temp = tempnam(sys_get_temp_dir(), 'workspace-update-') . '.phar';

        try {
            $this->output->infof('Downloading new version (%s) from %s', $release->getVersion(), $release->getUrl());
            $releaseData = @file_get_contents($release->getUrl());
            if ($releaseData === false) {
                throw new \RuntimeException(sprintf('Unable to download latest release at %s', $release->getUrl()), self::CODE_ERR_FETCHING_NEXT_RELEASE);
            }

            $this->output->infof('Writing to %s', $temp);
            if (file_put_contents($temp, $releaseData) === false) {
                throw new \RuntimeException(sprintf('Unable to write to %s', $temp));
            }

            if (chmod($temp, 0755 & ~umask()) === false) {
                throw new \RuntimeException(sprintf('Unable to set permissions on %s', $temp));
            }

            $this->output->info('Validating the downloaded phar...');
            $phar = new \Phar($temp);
            unset($phar);
            $this->output->infof('Download OK. Copying into place at %s', $targetPath);
            rename($temp, $targetPath);
            $this->output->success('Done.');
        } catch (\Error $e) {
            @unlink($temp);
            throw new \RuntimeException('Error occurred processing the update: ' . $e->getMessage(), 0, $e);
        }
    }

    private function getLatestRelease(): Release
    {
        try {
            $release = file_get_contents($this->apiUrl . '/latest', false, $this->createStreamContext());
        } catch (\Throwable $e) {
            throw new \RuntimeException('Error fetching latest release from GitHub.', self::CODE_ERR_FETCHING_RELEASES);
        }

        $latest = json_decode($release);

        return new Release($latest->assets[0]->browser_download_url, $latest->tag_name);
    }

    private function getLatestReleaseByConstraint(string $targetConstraint): Release
    {
        try {
            $releasesRaw = file_get_contents($this->apiUrl, false, $this->createStreamContext());
        } catch (\Throwable $e) {
            throw new \RuntimeException('Error fetching latest release from GitHub.', self::CODE_ERR_FETCHING_RELEASES);
        }

        $versionParser = new VersionParser();

        $parts = explode('@', $targetConstraint);
        $constraint = $parts[0];
        if (count($parts) > 1) {
            $minStability = VersionParser::normalizeStability($parts[1]);
        } else {
            $minStability = $versionParser->parseStability($constraint);
        }

        $releases = json_decode($releasesRaw);
        $sortedVersions = Semver::rsort(array_map(fn ($release) => $release->tag_name, $releases));
        $filteredVersions = Semver::satisfiedBy($sortedVersions, $constraint);
        $filteredStabilityVersions = $this->filterVersionsByMinStability($versionParser, $filteredVersions, $minStability);
        $filteredReleases = $this->filterReleasesByVersions($releases, $filteredStabilityVersions);

        if (count($filteredReleases) == 0) {
            throw new \RuntimeException(sprintf('No releases match the version constraint "%s".', $targetConstraint), self::CODE_ERR_FETCHING_RELEASES);
        }

        $latest = $filteredReleases[0];

        return new Release($latest->assets[0]->browser_download_url, $latest->tag_name);
    }

    /**
     * @param string[] $versions
     *
     * @return string[]
     */
    private function filterVersionsByMinStability(VersionParser $versionParser, array $versions, string $minStability): array
    {
        return array_filter($versions, fn ($version) => self::$stabilities[$versionParser->parseStability($version)] <= self::$stabilities[$minStability]);
    }

    /**
     * @param object[] $releases
     * @param string[] $versions
     *
     * @return object[]
     */
    private function filterReleasesByVersions(array $releases, array $versions): array
    {
        return array_values(array_filter($releases, fn ($release) => in_array($release->tag_name, $versions)));
    }

    /**
     * @return resource
     */
    private function createStreamContext()
    {
        return stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => ['User-Agent: my127/workspace PHP self-updater'],
            ],
        ]);
    }
}
