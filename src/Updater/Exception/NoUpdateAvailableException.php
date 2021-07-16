<?php

namespace my127\Workspace\Updater\Exception;

use RuntimeException;

class NoUpdateAvailableException extends RuntimeException
{
    private $currentVersion;

    public function __construct(string $currentVersion)
    {
        parent::__construct('There is no update available for workspace.');

        $this->currentVersion = $currentVersion;
    }

    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }
}