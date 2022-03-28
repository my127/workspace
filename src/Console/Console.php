<?php

namespace my127\Workspace\Console;

use my127\Workspace\Console\Application\Action\ActionCollection;
use my127\Workspace\Console\Application\Application;
use my127\Workspace\Console\Application\Executor;
use my127\Workspace\Console\Application\Plugin\ContextualHelpPlugin;
use my127\Workspace\Console\Application\Plugin\VersionInfoPlugin;
use my127\Workspace\Console\Usage\Model\OptionDefinitionCollection;
use my127\Workspace\Console\Usage\Parser\OptionDefinitionParser;
use my127\Workspace\Console\Usage\Parser\UsageParserBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Console
{
    public static function application($name, $description = "", $version = '1.0'): Application
    {
        $dispatcher = new EventDispatcher();
        $executor   = new Executor($dispatcher, new UsageParserBuilder(), new OptionDefinitionParser(), new ActionCollection());

        $application = new Application($name, $description, $version, $executor, $dispatcher);
        $application->plugin(new ContextualHelpPlugin());
        $application->plugin(new VersionInfoPlugin());

        return $application;
    }

    public static function usage($definition, $cmd = null, OptionDefinitionCollection $optionRepository = null)
    {
        $cmd         = empty($cmd) ? [] : preg_split('/\s+/', $cmd);
        $usageParser = (new UsageParserBuilder())->createUsageParser($definition, $optionRepository);

        return $usageParser->parse($cmd);
    }
}
