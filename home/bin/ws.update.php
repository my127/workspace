<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../config/_compiled/container.php';

use my127\Workspace\Application;
use my127\Workspace\Updater\Exception\NoUpdateAvailableException;
use my127\Workspace\Updater\Updater;

$localFilename = realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];

$updater = new Updater('https://api.github.com/repos/my127/workspace/releases');

try {
    $updater->update(Application::getVersion(), $localFilename);
} catch (NoUpdateAvailableException $e) {
    echo sprintf("You are already running the latest version of workspace: %s", $e->getCurrentVersion()) . PHP_EOL;
    exit(1);
} catch (RuntimeException $e) {
    echo sprintf("%s. Aborting self-update", $e->getMessage()) . PHP_EOL;
    exit(1);
}
