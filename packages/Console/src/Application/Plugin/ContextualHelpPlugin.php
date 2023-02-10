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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ContextualHelpPlugin implements Plugin
{
    /**
     * @var Section
     */
    private $root;

    public function __construct(private OptionDefinitionParser $optionDefinitionParser, private ConsoleOutputInterface $output)
    {
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
                    if ($e->getInputSequence()->count() > 2 || is_null($e->getOptions()->find('help'))) {
                        $style = new SymfonyStyle(new ArrayInput([]), $this->output->getErrorOutput());
                        $style->error(sprintf('Command "%s" not recognised', $e->getInputSequence()->toArgumentString()));
                    }
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
        $this->output->writeln(sprintf('%s', $section->getDescription() ?: $section->getName()));
        $this->output->writeln('');

        if (count($section->getUsageDefinitions()) > 0) {
            $this->output->writeln('<fg=yellow>Usage:</>');
            foreach ($section->getUsageDefinitions() as $usageDefinition) {
                $this->output->writeln(sprintf('  %s', $usageDefinition));
            }
            $this->output->writeln('');
        } elseif ($section->getAction() !== null) {
            $this->output->writeln('<fg=yellow>Usage:</>');
            $this->output->writeln(sprintf('  %s [options]', $section->getName()));
            $this->output->writeln('');
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

    /**
     * @return void
     */
    private function displaySubCommandHelp(Section $section)
    {
        if (empty($children = $section->getChildren())) {
            return;
        }

        $this->output->writeln('<fg=yellow>Sub Commands:</>');

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
            $this->output->writeln(sprintf('  <fg=green>%s</>%s', str_pad($line['name'], $padding), $line['description']));
        }
    }

    /**
     * @param list<OptionDefinition> $options
     */
    private function displayOptionsHelp(string $heading, array $options): void
    {
        $padding = 0;
        $lines = [];

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
            $this->output->writeln($heading);

            foreach ($lines as $line) {
                $this->output->writeln(sprintf('<fg=green>%s</>%s', str_pad($line['definition'], $padding), $line['description']));
            }
            $this->output->writeln('');
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
