<?php

namespace my127\Console\Application\Plugin;

use my127\Console\Application\Application;
use my127\Console\Application\Event\BeforeActionEvent;
use my127\Console\Application\Event\InvalidUsageEvent;
use my127\Console\Application\Executor;
use my127\Console\Application\Section\Section;
use my127\Console\Usage\Exception\NoSuchOptionException;
use my127\Console\Usage\Model\BooleanOptionValue;
use my127\Console\Usage\Model\OptionDefinition;
use my127\Console\Usage\Model\OptionDefinitionCollection;
use my127\Console\Usage\Parser\OptionDefinitionParser;

class ContextualHelpPlugin implements Plugin
{
    /**
     * @var Section
     */
    private $root;

    /**
     * @var OptionDefinitionParser
     */
    private $optionDefinitionParser;

    public function __construct(OptionDefinitionParser $optionDefinitionParser)
    {
        $this->optionDefinitionParser = $optionDefinitionParser;
    }

    public function setup(Application $application): void
    {
        $this->root = $application->getRootSection();

        $application
            ->option('-h, --help    Show help message')
            ->on(
                Executor::EVENT_BEFORE_ACTION,
                function (BeforeActionEvent $e) {
                    try {
                        if (($input = $e->getInput())->getOption('help')->equals(BooleanOptionValue::create(true))) {
                            $this->displayHelpPage($this->root->get(implode(' ', $input->getCommand())));
                            $e->preventAction();
                        }
                    } catch (NoSuchOptionException $e) {
                        // Ignore actions that does not provide help.
                    }
                }
            )
            ->on(
                Executor::EVENT_INVALID_USAGE,
                function (InvalidUsageEvent $e) {
                    $argv = $e->getInputSequence();
                    $parts = [];

                    while ($positional = $argv->pop()) {
                        $parts[] = $positional;
                    }

                    $name = implode(' ', $parts);
                    $section = $this->root->contains($name) ? $this->root->get($name) : $this->root;

                    $this->displayHelpPage($section);
                }
            );
    }

    private function displayHelpPage(Section $section): void
    {
        // Description
        echo "\n\033[1m" . ($section->getDescription() ?: $section->getName()) . "\033[0m\n\n";

        // Usage
        if (count($section->getUsageDefinitions()) > 0) {
            echo "\033[33mUsage:\033[0m\n";
            foreach ($section->getUsageDefinitions() as $usageDefinition) {
                echo "  {$usageDefinition}\n";
            }
            echo "\n\n";
        } elseif ($section->getAction() !== null) {
            echo "\033[33mUsage:\033[0m\n";
            echo "  {$section->getName()} [options]";
            echo "\n\n";
        }

        // Command Options
        if (!$this->isRoot($section)) {
            $this->displayOptionsHelp("\033[33mCommand Options:\033[0m", $section->getOptions());
        }

        // Sub Commands
        $this->displaySubCommandHelp($section);

        // Global Options
        $this->displayOptionsHelp("\033[33mGlobal Options:\033[0m", $this->root->getOptions());
    }

    private function displaySubCommandHelp(Section $section)
    {
        if (empty($children = $section->getChildren())) {
            return;
        }

        echo "\033[33mSub Commands:\033[0m\n";

        $lines = [];
        $padding = 0;

        /**
         * @var Section $child
         */
        foreach ($children as $child) {
            $name = $child->getName();
            $line = [
                'name' => substr($name, strrpos($name, ' ')),
                'description' => $child->getDescription(),
            ];

            if (($length = strlen($name)) > $padding) {
                $padding = $length;
            }

            $lines[] = $line;
        }

        $padding += 4;

        foreach ($lines as $line) {
            echo '  ' . "\033[32m" . str_pad($line['name'], $padding) . "\033[0m" . $line['description'] . "\n";
        }

        echo "\n";
    }

    private function displayOptionsHelp(string $heading, array $options): void
    {
        $padding = 0;
        $lines = [];

        /**
         * @var OptionDefinition $option
         */
        foreach ($this->getOptionCollection($options) as $option) {
            $description = $option->getDescription();

            $definition = '  ';
            $definition .= $option->getShortName() ? '-' . $option->getShortName() . ', ' : '    ';
            $definition .= $option->getLongName() ? '--' . $option->getLongName() : '';
            $definition .= $option->getType() == OptionDefinition::TYPE_VALUE ? '=<' . $option->getArgument() . '>' : '';

            if (($length = strlen($definition)) > $padding) {
                $padding = $length;
            }

            $lines[] = ['definition' => $definition, 'description' => $description];
        }

        $padding += 4;

        if (!empty($lines)) {
            echo $heading . "\n";

            foreach ($lines as $line) {
                echo "\033[32m" . str_pad($line['definition'], $padding) . "\033[0m" . $line['description'] . "\n";
            }

            echo "\n";
        }
    }

    private function isRoot($section): bool
    {
        return $section === $this->root;
    }

    private function getOptionCollection(array $options): OptionDefinitionCollection
    {
        $collection = new OptionDefinitionCollection();

        foreach ($options as $option) {
            $collection->add($this->optionDefinitionParser->parse($option));
        }

        return $collection;
    }
}
