<?php

namespace my127\Workspace\Updater;

use Error;
use my127\Workspace\Updater\Exception\NoUpdateAvailableException;
use my127\Workspace\Updater\Exception\NoVersionDeterminedException;
use Phar;
use RuntimeException;
use Throwable;

class Updater
{
    public const CODE_ERR_FETCHING_RELEASES = 100;
    public const CODE_NO_RELEASES = 101;
    public const CODE_ERR_FETCHING_NEXT_RELEASE = 102;

    /** @var string */
    private $apiUrl;

    /** @var StdOutput */
    private $output;

    public function __construct(string $apiUrl, ?Output $output = null)
    {
        $this->apiUrl = $apiUrl;
        $this->output = $output ?: new StdOutput();
    }

    public function update(string $currentVersion, string $targetPath)
    {
        if (empty($currentVersion)) {
            throw new NoVersionDeterminedException();
        }

        $latest = $this->getLatestRelease();
        if (!$latest->isMoreRecentThan($currentVersion)) {
            throw new NoUpdateAvailableException($currentVersion);
        }

        try {
            $this->output->infof('Downloading new version (%s) from %s', $latest->getVersion(), $latest->getUrl());
            $releaseData = @file_get_contents($latest->getUrl());
            if ($releaseData === false) {
                throw new RuntimeException(sprintf('Unable to download latest release at %s', $latest->getUrl()), self::CODE_ERR_FETCHING_NEXT_RELEASE);
            }

            $temp = tempnam(sys_get_temp_dir(), 'workspace-update-') . '.phar';
            $this->output->infof('Writing to %s', $temp);
            if (file_put_contents($temp, $releaseData) === false) {
                throw new RuntimeException(sprintf('Unable to write to %s', $temp));
            }

            if (chmod($temp, 0755 & ~umask()) === false) {
                throw new RuntimeException(sprintf('Unable to set permissions on %s', $temp));
            }

            $this->output->info('Validating the downloaded phar...');
            $phar = new Phar($temp);
            unset($phar);
            $this->output->infof('Download OK. Copying into place at %s', $targetPath);
            rename($temp, $targetPath);
        } catch (Error $e) {
            @unlink($temp);
            throw new RuntimeException('Error occurred processing the update: ' . $e->getMessage(), 0, $e);
        }
    }

    private function getLatestRelease(): Release
    {
        try {
            $releases = file_get_contents($this->apiUrl, false, $this->createStreamContext());
        } catch (Throwable $e) {
            throw new RuntimeException('Error fetching releases from GitHub.', self::CODE_ERR_FETCHING_RELEASES);
        }

        $releases = json_decode($releases);

        if (count($releases) === 0) {
            throw new RuntimeException('No releases present in the GitHub API response.', self::CODE_NO_RELEASES);
        }

        $latest = $releases[0];

        return new Release($latest->assets[0]->browser_download_url, $latest->tag_name);
    }

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
