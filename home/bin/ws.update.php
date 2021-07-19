<?php

use my127\Workspace\Application;
use my127\Workspace\Updater\Exception\NoUpdateAvailableException;
use my127\Workspace\Updater\Exception\NoVersionDeterminedException;
use my127\Workspace\Updater\Updater;

$pharPath = Phar::running(false);
if (empty($pharPath)) {
    echo 'This command can only be executed from within the ws utility.' . PHP_EOL;
    exit(1);
}

$updater = new Updater('https://api.github.com/repos/my127/workspace/releases');

try {
    $updater->update(Application::getVersion(), $pharPath);
} catch (NoUpdateAvailableException $e) {
    echo sprintf('You are already running the latest version of workspace: %s', $e->getCurrentVersion()) . PHP_EOL;
    exit(1);
} catch (NoVersionDeterminedException $e) {
    echo 'Unable to determine your current workspace version. You are likely not using a tagged released.' . PHP_EOL;
    exit(1);
} catch (RuntimeException $e) {
    echo sprintf('%s. Aborting self-update', $e->getMessage()) . PHP_EOL;
    exit(1);
}
