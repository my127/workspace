<?php

namespace my127\Workspace\Updater\Plugin;

use my127\Console\Application\Application;
use my127\Console\Application\Plugin\Plugin;
use my127\Workspace\Application as BaseApplication;
use my127\Workspace\Updater\Exception\NoUpdateAvailableException;
use my127\Workspace\Updater\Exception\NoVersionDeterminedException;
use my127\Workspace\Updater\Updater;
use Phar;
use RuntimeException;

class Command implements Plugin
{
    /** @var Updater */
    private $updater;

    public function __construct(Updater $updater)
    {
        $this->updater = $updater;
    }

    public function setup(Application $application): void
    {
        $application->section('self-update')
            ->description('Updates the current version of workspace.')
            ->action($this->action());
    }

    private function action()
    {
        return function () {
            $pharPath = Phar::running(false);
            if (empty($pharPath)) {
                echo 'This command can only be executed from within the ws utility.' . PHP_EOL;
                exit(1);
            }

            try {
                $this->updater->update(BaseApplication::getVersion(), $pharPath);
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
        };
    }
}
