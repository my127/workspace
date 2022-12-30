<?php

namespace my127\Console;

use my127\Console\Application\Action\ActionCollection;
use my127\Console\Application\Application;
use my127\Console\Application\Executor;
use my127\Console\Application\Plugin\ContextualHelpPlugin;
use my127\Console\Application\Plugin\VersionInfoPlugin;
use my127\Console\Console\EchoOutput;
use my127\Console\Factory\OptionValueFactory;
use my127\Console\Usage\Input;
use my127\Console\Usage\Model\OptionDefinitionCollection;
use my127\Console\Usage\Parser\OptionDefinitionParser;
use my127\Console\Usage\Parser\UsageParserBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Console
{
    public static function application($name, $description = '', $version = '1.0'): Application
    {
        $dispatcher = new EventDispatcher();
        $optionValueFactory = new OptionValueFactory();
        $optionDefinitionParser = new OptionDefinitionParser($optionValueFactory);
        $executor = new Executor(
            $dispatcher,
            new UsageParserBuilder($optionValueFactory),
            $optionDefinitionParser,
            new ActionCollection(),
            $optionValueFactory
        );

        $application = new Application($executor, $dispatcher, $name, $description, $version);
        $application->plugin(new ContextualHelpPlugin($optionDefinitionParser, new EchoOutput()));
        $application->plugin(new VersionInfoPlugin());

        return $application;
    }

    public static function usage($definition, $cmd = null, OptionDefinitionCollection $optionRepository = null): Input|bool
    {
        $cmd = empty($cmd) ? [] : preg_split('/\s+/', $cmd);
        $optionValueFactory = new OptionValueFactory();
        $usageParser = (new UsageParserBuilder($optionValueFactory))->createUsageParser($definition, $optionRepository);

        return $usageParser->parse($cmd);
    }
}
