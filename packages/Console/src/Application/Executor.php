<?php

namespace my127\Console\Application;

use my127\Console\Application\Action\ActionCollection;
use my127\Console\Application\Event\BeforeActionEvent;
use my127\Console\Application\Event\InvalidUsageEvent;
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
    public const EVENT_INVALID_USAGE = 'my127.console.application.invalid_usage';
    public const EVENT_BEFORE_ACTION = 'my127.console.application.before_action';

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
        $this->dispatcher         = $dispatcher;
        $this->optionParser       = $optionParser;
        $this->usageParserBuilder = $usageParserBuilder;
        $this->actions            = $actions;
        $this->optionValueFactory = $optionValueFactory;
    }

    public function getActionCollection(): ActionCollection
    {
        return $this->actions;
    }

    public function run(Section $section, $argv = []): void
    {
        $this->argv = $argv;

        $this->root = $section;
        $this->root->accept($this);

        if ($this->matchedSection === null || $this->matchedInput === null) {
            $this->invalidUsage($argv);
            return;
        }

        if ($this->beforeAction()->isActionPrevented()) {
            return;
        }

        if (($action = $this->matchedSection->getAction()) === null) {
            return;
        }

        $this->invokeAction($action);
    }

    public function visit(Section $section): bool
    {
        $options = $this->buildOptionCollection($this->root->getOptions())
            ->merge($this->buildOptionCollection($section->getOptions()));


        $usageDefinitions = $section->getUsageDefinitions();

        if (empty($usageDefinitions) && $section->getAction() !== null) {
            $usageDefinitions = [$section->getName().' '.'[options]'];
        }

        foreach ($usageDefinitions as $usageDefinition) {
            if ($usageDefinition[-1] == '%') {
                $compare = explode(' ', substr($usageDefinition, 0, -2));
                $against = array_slice($this->argv, 0, count($compare));

                if ($compare != $against) {
                    continue;
                }

                $options = new OptionDefinitionCollection();
                $args    = [];

                foreach ($compare as $command) {
                    $args[] = new Command($command);
                }

                $args[] = new Argument('%', implode(' ', array_slice($this->argv, count($compare))));

                $this->matchedInput   = new Input($args, $options, $this->optionValueFactory);
                $this->matchedSection = $section;

                return false;
            } else {
                $parser = $this->usageParserBuilder->createUsageParser($usageDefinition, $options);

                if (($input = $parser->parse($this->argv)) !== false) {
                    $this->matchedInput   = $input;
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

    private function invalidUsage($argv): InvalidUsageEvent
    {
        $this->dispatcher->dispatch(
            self::EVENT_INVALID_USAGE,
            $event = new InvalidUsageEvent(
                $argv,
                $this->buildOptionCollection($this->root->getOptions())
            )
        );

        return $event;
    }

    private function beforeAction(): BeforeActionEvent
    {
        $this->dispatcher->dispatch(
            self::EVENT_BEFORE_ACTION,
            $event = new BeforeActionEvent(
                $this->matchedInput,
                $this->matchedSection
            )
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
