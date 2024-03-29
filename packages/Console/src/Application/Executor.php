<?php

namespace my127\Console\Application;

use my127\Console\Application\Action\ActionCollection;
use my127\Console\Application\Event\BeforeActionEvent;
use my127\Console\Application\Event\DisplayUsageEvent;
use my127\Console\Application\Section\Section;
use my127\Console\Application\Section\SectionVisitor;
use my127\Console\Factory\OptionValueFactory;
use my127\Console\Usage\Input;
use my127\Console\Usage\Model\Argument;
use my127\Console\Usage\Model\Command;
use my127\Console\Usage\Model\OptionDefinitionCollection;
use my127\Console\Usage\Parser\OptionDefinitionParser;
use my127\Console\Usage\Parser\UsageParserBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Executor implements SectionVisitor
{
    public const EVENT_DISPLAY_USAGE = 'my127.console.application.display_usage';
    public const EVENT_BEFORE_ACTION = 'my127.console.application.before_action';
    public const EXIT_OK = 0;
    public const EXIT_ERROR = 1;
    public const EXIT_COMMAND_NOT_FOUND = 127;

    /**
     * @var EventDispatcher
     */
    private $dispatcher = null;

    /**
     * @var OptionDefinitionParser
     */
    private $optionParser = null;

    /**
     * @var UsageParserBuilder
     */
    private $usageParserBuilder = null;

    /**
     * @var Section
     */
    private $root = null;

    /**
     * @var string[]
     */
    private $argv = [];

    /**
     * @var Section
     */
    private $matchedSection = null;

    /**
     * @var Input
     */
    private $matchedInput = null;

    /**
     * @var ActionCollection
     */
    private $actions;

    /**
     * @var OptionValueFactory
     */
    private $optionValueFactory;

    public function __construct(
        EventDispatcher $dispatcher,
        UsageParserBuilder $usageParserBuilder,
        OptionDefinitionParser $optionParser,
        ActionCollection $actions,
        OptionValueFactory $optionValueFactory
    ) {
        $this->dispatcher = $dispatcher;
        $this->optionParser = $optionParser;
        $this->usageParserBuilder = $usageParserBuilder;
        $this->actions = $actions;
        $this->optionValueFactory = $optionValueFactory;
    }

    public function getActionCollection(): ActionCollection
    {
        return $this->actions;
    }

    public function run(Section $section, $argv = []): int
    {
        $this->argv = $argv;

        $this->root = $section;
        $this->root->accept($this);

        if ($this->matchedSection === null || $this->matchedInput === null) {
            if (count($argv) == 1 || (count($argv) == 2 && ($argv[1] == '--help' || $argv[1] == '-h'))) {
                $this->displayUsage($argv, true);

                return self::EXIT_OK;
            } else {
                $this->displayUsage($argv, false);

                return self::EXIT_COMMAND_NOT_FOUND;
            }
        }

        if ($this->beforeAction()->isActionPrevented()) {
            return self::EXIT_OK;
        }

        if (($action = $this->matchedSection->getAction()) === null) {
            return self::EXIT_OK;
        }

        $this->invokeAction($action);

        return 0;
    }

    public function visit(Section $section): bool
    {
        $options = $this->buildOptionCollection($this->root->getOptions())
            ->merge($this->buildOptionCollection($section->getOptions()));

        $usageDefinitions = $section->getUsageDefinitions();

        if (empty($usageDefinitions) && $section->getAction() !== null) {
            $usageDefinitions = [$section->getName() . ' [options]'];
        }

        foreach ($usageDefinitions as $usageDefinition) {
            if ($usageDefinition[-1] == '%') {
                $compare = explode(' ', substr($usageDefinition, 0, -2));
                $against = array_slice($this->argv, 0, count($compare));

                if ($compare != $against) {
                    continue;
                }

                $options = new OptionDefinitionCollection();
                $args = [];

                foreach ($compare as $command) {
                    $args[] = new Command($command);
                }

                $args[] = new Argument('%', implode(' ', array_slice($this->argv, count($compare))));

                $this->matchedInput = new Input($args, $options, $this->optionValueFactory);
                $this->matchedSection = $section;

                return false;
            } else {
                $parser = $this->usageParserBuilder->createUsageParser($usageDefinition, $options);

                if (($input = $parser->parse($this->argv)) !== false) {
                    $this->matchedInput = $input;
                    $this->matchedSection = $section;

                    return false;
                }
            }
        }

        return true;
    }

    private function buildOptionCollection($options = []): OptionDefinitionCollection
    {
        $collection = new OptionDefinitionCollection();

        foreach ($options as $option) {
            $collection->add($this->optionParser->parse($option));
        }

        return $collection;
    }

    private function displayUsage($argv, $validCommand): DisplayUsageEvent
    {
        $this->dispatcher->dispatch(
            $event = new DisplayUsageEvent(
                $argv,
                $this->buildOptionCollection($this->root->getOptions()),
                $validCommand
            ),
            self::EVENT_DISPLAY_USAGE
        );

        return $event;
    }

    private function beforeAction(): BeforeActionEvent
    {
        $this->dispatcher->dispatch(
            $event = new BeforeActionEvent(
                $this->matchedInput,
                $this->matchedSection
            ),
            self::EVENT_BEFORE_ACTION
        );

        return $event;
    }

    private function invokeAction($action)
    {
        if (!is_callable($action)) {
            $action = $this->actions->get($action);
        }

        $action($this->matchedInput);
    }
}
