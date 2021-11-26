<?php

namespace my127\Workspace;

use my127\Console\Application\Application as ConsoleApplication;
use my127\Console\Application\Executor;
use my127\Workspace\Environment\Environment;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Application extends ConsoleApplication
{
    public const VERSION = 'alpha1';

    /** @var Environment */
    private $environment;

    public function __construct(Environment $environment, Executor $executor, EventDispatcher $dispatcher)
    {
        parent::__construct($executor, $dispatcher, 'ws', '', self::VERSION);
        $this->environment = $environment;
    }

    public function run(?array $argv = null): void
    {
        $this->environment->build();
        parent::run($argv);
    }
}
