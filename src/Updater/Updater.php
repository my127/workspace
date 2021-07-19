<?php

namespace my127\Workspace\Updater;

use Error;
use my127\Workspace\Updater\Exception\NoUpdateAvailableException;
use Phar;
use RuntimeException;

class Updater
{
    /** @var string */
    private $apiUrl;

    /** @var Output */
    private $output;

    public function __construct(string $apiUrl)
    {
        $this->apiUrl = $apiUrl;
        $this->output = new Output();
    }

    public function update(string $currentVersion, string $targetPath)
    {
        $latest = $this->getLatestRelease();
        if (!$latest->isMoreRecentThan($currentVersion)) {
            throw new NoUpdateAvailableException($currentVersion);
        }

        try {
            $this->output->infof('Downloading new version (%s) from %s', $latest->getVersion(), $latest->getUrl());
            $releaseData = @file_get_contents($latest->getUrl());
            if ($releaseData === false) {
                throw new RuntimeException(sprintf('Unable to download latest release at %s', $latest->getUrl()));
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
        $releases = file_get_contents($this->apiUrl, false, $this->createStreamContext());
        if ($releases === false) {
            throw new RuntimeException('Error fetching releases from GitHub.');
        }

        $releases = json_decode($releases);

        if (count($releases) === 0) {
            throw new RuntimeException('No releases present in the GitHub API response.');
        }

        $latest = $releases[0];

        return new Release($latest->assets[0]->browser_download_url, $latest->tag_name);
    }

    private function createStreamContext()
    {
        return stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => ['User-Agent: my127/workspace PHP self-updater']
            ]
        ]);
    }
}