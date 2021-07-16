<?php

namespace my127\Workspace\Updater;

class Release
{
    /** @var string */
    private $url;

    /** @var string */
    private $version;

    public function __construct(string $url, string $version)
    {
        $this->url = $url;
        $this->version = $version;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Will return true if this release is more recent than the version provided.
     *
     * @param string $version Semver version number, e.g. 1.0.1, 0.2.0-alpha1 etc.
     * @return bool
     */
    public function isMoreRecentThan(string $version): bool
    {
        return version_compare($this->getVersion(), $version, '>');
    }
}
