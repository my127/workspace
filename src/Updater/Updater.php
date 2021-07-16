<?php

namespace my127\Workspace\Updater;

use Error;
use my127\Workspace\Updater\Exception\NoUpdateAvailableException;
use Phar;

class Updater
{
    /** @var string */
    private $apiUrl;

    public function __construct(string $apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    public function update(string $currentVersion, string $targetPath)
    {
        $latest = $this->getLatestRelease();
        if (!$latest->isMoreRecentThan($currentVersion)) {
            throw new NoUpdateAvailableException($currentVersion);
        }

        try {
            $releaseData = file_get_contents($latest->getUrl());
            $temp = tempnam(sys_get_temp_dir(), 'workspace-update-');
            file_put_contents($temp, $releaseData);
            $phar = new Phar($temp);
            unset($phar);
            rename($temp, $targetPath);
        } catch (Error $e) {
            @unlink($temp);
            throw new \RuntimeException('Error occurred processing the update: ' . $e->getMessage(), 0, $e);
        }
    }

    private function getLatestRelease(): Release
    {
        $releases = file_get_contents($this->apiUrl, false, $this->createStreamContext());
        if ($releases === false) {
            throw new \RuntimeException('Error fetching releases from GitHub.');
        }

        $releases = json_decode($releases);

        if (count($releases) === 0) {
            throw new \RuntimeException('No releases present in the GitHub API response.');
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